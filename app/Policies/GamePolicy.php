<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Game $game): bool
    {
        return true; // All can view games
    }

    public function startGame(User $user, Game $game): bool
    {
        // Organizer or tournament creator can start game
        $tournament = $game->tournament;
        return $user->id === $tournament->created_by || $user->hasRole('admin');
    }

    public function recordResult(User $user, Game $game): bool
    {
        // Either player can record result, or organizer
        $tournament = $game->tournament;
        
        $isPlayer = $user->id === $game->player1_id || $user->id === $game->player2_id;
        $isOrganizer = $user->id === $tournament->created_by || $user->hasRole('admin');
        
        return $isPlayer || $isOrganizer;
    }
}
