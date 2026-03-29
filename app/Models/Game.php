<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'tournament_id',
        'round_id',
        'player1_id',
        'player2_id',
        'result',
        'status',
        'notes',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    const RESULT_DRAW = 'draw';
    const RESULT_PLAYER1_WIN = 'player1_win';
    const RESULT_PLAYER2_WIN = 'player2_win';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function recordResult(string $result): void
    {
        $this->result = $result;
        $this->status = self::STATUS_COMPLETED;
        $this->ended_at = now();
        $this->save();
        
        $this->updatePlayerScores();
    }

    private function updatePlayerScores(): void
    {
        $tournament = $this->tournament;
        
        if ($this->result === self::RESULT_PLAYER1_WIN) {
            $tournament->players()->syncWithoutDetaching([
                $this->player1_id => [
                    'wins' => DB::raw('wins + 1'),
                    'score' => DB::raw('score + 1'),
                ]
            ]);
            if ($this->player2_id) {
                $tournament->players()->syncWithoutDetaching([
                    $this->player2_id => [
                        'losses' => DB::raw('losses + 1'),
                    ]
                ]);
            }
        } elseif ($this->result === self::RESULT_PLAYER2_WIN) {
            $tournament->players()->syncWithoutDetaching([
                $this->player2_id => [
                    'wins' => DB::raw('wins + 1'),
                    'score' => DB::raw('score + 1'),
                ]
            ]);
            $tournament->players()->syncWithoutDetaching([
                $this->player1_id => [
                    'losses' => DB::raw('losses + 1'),
                ]
            ]);
        } elseif ($this->result === self::RESULT_DRAW) {
            $tournament->players()->syncWithoutDetaching([
                $this->player1_id => [
                    'draws' => DB::raw('draws + 1'),
                    'score' => DB::raw('score + 0.5'),
                ]
            ]);
            if ($this->player2_id) {
                $tournament->players()->syncWithoutDetaching([
                    $this->player2_id => [
                        'draws' => DB::raw('draws + 1'),
                        'score' => DB::raw('score + 0.5'),
                    ]
                ]);
            }
        }
    }
}
