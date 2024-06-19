<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Workshop;
use Carbon\Carbon;

class workshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $w = Workshop::create([
            'title_fr' => 'Histoire des maths',
            'title_en' => 'Maths history',
            'description' => json_encode([
                'fr' => 'Découvrez l\'histoire passionnante des mathématiques des origines à nos jours',
                'en' => 'Discover the fascinating history of mathematics from the origins to the present day'
            ]),
            'language' => 'fr',
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'campus' => 'BPR',
                'schedule' => [
                    ['day' => 'Monday', 'start' => '17:30', 'finish' => '18:30']
                ],
                'maxStudents' => 15
            ]),
            'organiser_id' => 1,
            'start_date' => (Carbon::now())->addWeek(1),
        ]);
        $w->themes()->attach([1,4]);

        $w = Workshop::create([
            'title_fr' => 'Introduction au Mahjong',
            'title_en' => 'Intro to Mahjong',
            'description' => json_encode([
                'fr' => 'Lancez-vous dans ce jeu ancestral chinois où se mèlent stratégie et tradition',
                'en' => 'Play this ancestral chinese game where strategy meets tradition'
            ]),
            'language' => 'both',
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'campus' => 'BPR',
                'schedule' => [
                    ['day' => 'Tuesday', 'start' => '12:30', 'finish' => '13:30']
                ],
                'maxStudents' => 12
            ]),
            'organiser_id' => 2,
            'start_date' => (Carbon::now())->addWeek(1),
        ]);

        $w->themes()->attach([4,5]);
    }
}
