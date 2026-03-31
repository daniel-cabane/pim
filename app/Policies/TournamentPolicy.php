<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can list tournaments
    }

    public function view(User $user, Tournament $tournament): bool
    {
        return true; // All authenticated users can view
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->created_by 
               || $tournament->isOrganiser($user)
               || $user->hasRole('admin');
    }

    public function delete(User $user, Tournament $tournament): bool
    {
        return ($user->id === $tournament->created_by || $user->hasRole('admin')) 
               && $tournament->status === 'draft';
    }

    public function start(User $user, Tournament $tournament): bool
    {
        return ($user->id === $tournament->isOrganiser($user) || $user->hasRole('admin'))
               && $tournament->canBeStarted();
    }

    public function joinTournament(User $user, Tournament $tournament): bool
    {
        return $tournament->canAddPlayer() && !$tournament->players()->where('users.id', $user->id)->exists();
    }
}
