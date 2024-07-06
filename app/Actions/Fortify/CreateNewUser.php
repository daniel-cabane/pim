<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $teacherEmails = [
            'adelettrez' => 'Antoine Delettrez',
            'bbarre' => 'Benjamin Barre',
            'jcabrit' => 'Jose Cabrit',
            'ngouez' => 'Nicolas Gouez',
            'dcabane' => 'Daniel Cabane',
            'rdelpuech' => 'Rémi Delpuech',
            'sparis' => 'Sébastien Paris',
            'ghenry' => 'Guillaume Henry',
            'tbelmekki' => 'Tarik Belmekki'
        ];
        $emailParts = explode('@', $user->email);

        if($user->google_id != null && $emailParts[1] == 'g.lfis.edu.hk'){
            if(isset($teacherEmails[$emailParts[0]])){
                $user->assignRole('teacher');
                $user->assignRole('publisher');
                $user->update([
                    'name' => $teacherEmails[$emailParts[0]],
                    'prefrence' => json_encode(['notifications' => 'all', 'title' => 'M.'])
                ]);
            } else if(is_numeric(substr($emailParts[0], -1))){
                $user->assignRole('student');
            }
        }

        return $user;
    }
}
