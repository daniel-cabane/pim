<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $role = Role::create(['name' => 'teacher']);
        $role = Role::create(['name' => 'hod']);
        $role = Role::create(['name' => 'admin']);

        $rootUser = User::firstOrCreate([
            'name'=>'Pim',
            'email'=>'pim@g.lfis.edu.hk',
            'email_verified_at' => now(),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' // secret
        ]);
        $rootUser->assignRole('admin');
        $rootUser->assignRole('teacher');

        // Themes seeder
        $themes = [
            ['title_en' => 'mathematics', 'title_fr' => 'mathématiques'],
            ['title_en' => 'computer science', 'title_fr' => 'informatique'],
            ['title_en' => 'creativity', 'title_fr' => 'créativité'],
            ['title_en' => 'culture', 'title_fr' => 'culture'],
            ['title_en' => 'games', 'title_fr' => 'jeux'],
            ['title_en' => 'strategy', 'title_fr' => 'stratégie'],
        ];
        foreach($themes as $theme) {
            Theme::create($theme);
        }
    }
}
