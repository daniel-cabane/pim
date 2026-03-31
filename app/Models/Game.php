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
        'result1',
        'result2',
        'status',
        'notes',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $appends = ['computed_status'];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFLICTED = 'conflicted';
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

    public function getComputedStatusAttribute(): string
    {
        if ($this->result1 === null || $this->result2 === null) {
            return self::STATUS_PENDING;
        }

        if ($this->resultsAreCoherent()) {
            return self::STATUS_COMPLETED;
        }

        return self::STATUS_CONFLICTED;
    }

    public function resultsAreCoherent(): bool
    {
        if ($this->result1 === null || $this->result2 === null) {
            return false;
        }

        // Coherent: player1 says win & player2 says loss, or vice versa, or both say draw
        return ($this->result1 === 'win' && $this->result2 === 'loss')
            || ($this->result1 === 'loss' && $this->result2 === 'win')
            || ($this->result1 === 'draw' && $this->result2 === 'draw');
    }

    public function reportResult(int $playerId, string $result): void
    {
        if ($playerId === $this->player1_id) {
            $this->result1 = $result;
        } elseif ($playerId === $this->player2_id) {
            $this->result2 = $result;
        }

        $this->status = $this->getComputedStatusAttribute();

        if ($this->status === self::STATUS_COMPLETED) {
            $this->ended_at = now();
        }

        $this->save();
    }

    public function setResultByOrganiser(string $result1, string $result2): void
    {
        $this->result1 = $result1;
        $this->result2 = $result2;
        $this->status = $this->getComputedStatusAttribute();

        if ($this->status === self::STATUS_COMPLETED) {
            $this->ended_at = now();
        }

        $this->save();
    }
}
