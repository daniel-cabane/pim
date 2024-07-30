<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SurveyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->hasRole('student') || $user->hasRole('teacher'));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Survey $survey): bool
    {
        if($survey->author_id == $user->id || $user->hasRole('admin') || $user->hasRole('hod')){
            return true;
        }
        if($user->hasRole('admin') && $survey->workshop->organiser_id == $user->id){
            return true;
        }
        if($survey->status == 'open'){
            if($survey->answers()->where('user_id', $user->id)->exists()){
                return true;
            }
            if($survey->workshop_id != null && $survey->workshop->applicants->contains($user)){
                $application = $survey->workshop->applicants()->where('user_id', $user->id)->first();
                if($application->pivot->confirmed){
                    return true;
                }
            }
        }
        return false;
    }

    public function submit(User $user, Survey $survey): bool
    {
        return $survey->answers()->where('user_id', $user->id)->exists() && $survey->status == 'open';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Survey $survey): bool
    {
        return $survey->author_id == $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Survey $survey): bool
    {
        return $survey->author_id == $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Survey $survey): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Survey $survey): bool
    {
        //
    }
}
