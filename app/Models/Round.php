<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'round_number',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function canStart(): bool
    {
        return $this->status === 'pending' && $this->games()->count() > 0;
    }

    public function allGamesCompleted(): bool
    {
        return $this->games()->where('status', '!=', 'completed')->count() === 0;
    }
}
