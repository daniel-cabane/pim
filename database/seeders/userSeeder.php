<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hod = User::firstOrCreate([
            'name'=>'HOD',
            'email'=>'hod',
            'email_verified_at' => now(),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'preferences' => ['notifications' => 'all', 'title' => 'M.']
        ]);
        $hod->assignRole('hod');
        $hod->assignRole('teacher');


        for($i=1 ; $i <= 2 ; $i++){
            $user = User::firstOrCreate([
                'name'=>"tea$i",
                'email'=>"tea$i",
                'email_verified_at' => now(),
                'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'preferences' => ['notifications' => 'all', 'title' => 'M.']
            ]);
            $user->assignRole('teacher');
        }

        for($i=1 ; $i <= 9 ; $i++){
            $user = User::firstOrCreate([
                'name'=>"stu$i",
                'email'=>"stu$i",
                'email_verified_at' => now(),
                'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'preferences' => ['notifications' => 'all']
            ]);
            $user->assignRole('student');
        }
    }
}
