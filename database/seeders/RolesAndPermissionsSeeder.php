<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Theme;
use Carbon\Carbon;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create roles and assign created permissions
        $role = Role::create(['name' => 'student']);
        $role = Role::create(['name' => 'publisher']);
        $role = Role::create(['name' => 'teacher']);
        $role = Role::create(['name' => 'hod']);
        $role = Role::create(['name' => 'admin']);

        $password = App::environment('local') ? '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' : Hash::make(Str::password());
        // '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' = 'secret'
        $rootUser = User::firstOrCreate([
            'name'=>'Pim',
            'email'=>'pim@g.lfis.edu.hk',
            'email_verified_at' => now(),
            'password' => $password,
            'preferences' => ['notifications' => 'all', 'title' => 'M.']
        ]);
        $rootUser->assignRole('admin');
        $rootUser->assignRole('teacher');
        $rootUser->assignRole('publisher');

        // Themes seeder
        $themes = [
            ['title_en' => 'mathematics', 'title_fr' => 'mathématiques', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'advanced', 'title_fr' => 'approfondissement', 'forWorkshop' => true, 'forPost' => false],
            ['title_en' => 'computer science', 'title_fr' => 'informatique', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'creativity', 'title_fr' => 'créativité', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'culture', 'title_fr' => 'culture', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'history', 'title_fr' => 'histoire', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'games', 'title_fr' => 'jeux', 'forWorkshop' => true, 'forPost' => true],
            ['title_en' => 'strategy', 'title_fr' => 'stratégie', 'forWorkshop' => true, 'forPost' => false],
            ['title_en' => 'professional development', 'title_fr' => 'formation professionnelle', 'forWorkshop' => true, 'forPost' => false]
        ];
        foreach($themes as $theme) {
            Theme::create($theme);
        }
    }
}
