<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Tournament;
use App\Models\User;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->get();

        // Tournament 1: 5 players, in preparation
        $t1 = Tournament::create([
            'name' => 'Spring Chess Open',
            'description' => 'A friendly 5-player Swiss tournament to kick off the spring season.',
            'format' => 'swiss',
            'status' => 'preparation',
            'rounds_count' => 0,
            'players_count' => 5,
            'created_by' => 1,
        ]);

        $t1->organisers()->attach($users->first()->id);

        $players1 = $users->take(5);
        foreach ($players1 as $player) {
            $t1->players()->attach($player->id, [
                'score' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'rating' => rand(600, 1800),
                'banned' => false,
            ]);
        }

        // Tournament 2: 8 players, in preparation
        $t2 = Tournament::create([
            'name' => 'Grand Pi Room Championship',
            'description' => 'The ultimate 8-player round robin championship of the Pi Room.',
            'format' => 'round_robin',
            'status' => 'preparation',
            'rounds_count' => 0,
            'players_count' => 8,
            'created_by' => 2,
        ]);

        $t2->organisers()->attach($users->first()->id);

        $players2 = $users->take(8);
        foreach ($players2 as $player) {
            $t2->players()->attach($player->id, [
                'score' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'rating' => rand(600, 1800),
                'banned' => false,
            ]);
        }
    }
}
