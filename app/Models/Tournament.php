<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'format',
        'status',
        'rounds_count',
        'players_count',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function getRouteKeyName(){
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function ($tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = static::generateUniqueSlug($tournament->name);
            }
        });

        static::updating(function ($tournament) {
            if ($tournament->isDirty('name')) {
                $tournament->slug = static::generateUniqueSlug($tournament->name);
            }
        });
    }

    protected static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'like', "{$slug}%")->count();
        
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }

    public function rounds()
    {
        return $this->hasMany(Round::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'tournament_player')
            ->withPivot('score', 'wins', 'draws', 'losses', 'rating', 'banned')
            ->withTimestamps();
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function organisers()
    {
        return $this->belongsToMany(User::class, 'tournament_organiser')
            ->withTimestamps();
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'preparation' && $this->players_count >= 2;
    }

    public function canAddPlayer(): bool
    {
        return $this->status === 'preparation';
    }

    public function getStandings()
    {
        return DB::table('users')
            ->join('tournament_player', 'users.id', '=', 'tournament_player.user_id')
            ->where('tournament_player.tournament_id', $this->id)
            ->where('tournament_player.banned', false)
            ->select(
                'users.id',
                'users.name',
                'tournament_player.score as points',
                'tournament_player.wins',
                'tournament_player.draws',
                'tournament_player.losses',
                'tournament_player.rating'
            )
            ->orderBy('tournament_player.score', 'DESC')
            ->orderBy('tournament_player.wins', 'DESC')
            ->orderBy('tournament_player.rating', 'ASC')
            ->get();
    }

    public function recalculateStandings(): void
    {
        // Reset all player stats to zero
        $this->players()->newPivotQuery()->update([
            'score' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
        ]);

        // Recalculate from all completed games
        $games = $this->games()->where('status', Game::STATUS_COMPLETED)->get();

        foreach ($games as $game) {
            if ($game->result1 === 'win') {
                $this->players()->syncWithoutDetaching([
                    $game->player1_id => [
                        'wins' => DB::raw('wins + 1'),
                        'score' => DB::raw('score + 1'),
                    ]
                ]);
                if ($game->player2_id) {
                    $this->players()->syncWithoutDetaching([
                        $game->player2_id => [
                            'losses' => DB::raw('losses + 1'),
                        ]
                    ]);
                }
            } elseif ($game->result1 === 'loss') {
                $this->players()->syncWithoutDetaching([
                    $game->player1_id => [
                        'losses' => DB::raw('losses + 1'),
                    ]
                ]);
                if ($game->player2_id) {
                    $this->players()->syncWithoutDetaching([
                        $game->player2_id => [
                            'wins' => DB::raw('wins + 1'),
                            'score' => DB::raw('score + 1'),
                        ]
                    ]);
                }
            } elseif ($game->result1 === 'draw') {
                $this->players()->syncWithoutDetaching([
                    $game->player1_id => [
                        'draws' => DB::raw('draws + 1'),
                        'score' => DB::raw('score + 0.5'),
                    ]
                ]);
                if ($game->player2_id) {
                    $this->players()->syncWithoutDetaching([
                        $game->player2_id => [
                            'draws' => DB::raw('draws + 1'),
                            'score' => DB::raw('score + 0.5'),
                        ]
                    ]);
                }
            }
        }
    }

    public function isOrganiser(User $user = null): bool
    {
        return $user ? $this->organisers()->where('user_id', $user->id)->exists() : false;
    }
}
