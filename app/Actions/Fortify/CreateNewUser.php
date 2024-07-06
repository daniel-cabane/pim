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
            'adelettrez' => ['name' => 'Antoine Delettrez', 'campus' => 'BPR', 'language' => 'both'],
            'bbarre' => ['name' => 'Benjamin Barre', 'campus' => 'both', 'language' => 'both'],
            'jcabrit' => ['name' => 'Jose Cabrit', 'campus' => 'BPR', 'language' => 'both'],
            'ngouez' => ['name' => 'Nicolas Gouez', 'campus' => 'BPR', 'language' => 'both'],
            'dcabane' => ['name' => 'Daniel Cabane', 'campus' => 'both', 'language' => 'both'],
            'rdelpuech' => ['name' => 'Rémi Delpuech', 'campus' => 'both', 'language' => 'both'],
            'sparis' => ['name' => 'Sébastien Paris', 'campus' => 'both', 'language' => 'both'],
            'ghenry' => ['name' => 'Guillaume Henry', 'campus' => 'both', 'language' => 'both'],
            'tbelmekki' => ['name' => 'Tarik Belmekki', 'campus' => 'both', 'language' => 'both']
        ];
        $emailParts = explode('@', $user->email);

        if($user->google_id != null && $emailParts[1] == 'g.lfis.edu.hk'){
            if(isset($teacherEmails[$emailParts[0]])){
                $user->assignRole('teacher');
                $user->assignRole('publisher');
                $user->update([
                    'name' => $teacherEmails[$emailParts[0]]['name'],
                    'prefrence' => json_encode([
                        'notifications' => 'all',
                        'title' => 'M.',
                        'campus' => $teacherEmails[$emailParts[0]]['campus'],
                        'language' => $teacherEmails[$emailParts[0]]['language']
                        ])
                ]);
            } else if(is_numeric(substr($emailParts[0], -1))){
                $user->assignRole('student');
            }
        }

        return $user;
    }
}
