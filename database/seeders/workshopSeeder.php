<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Workshop;
use App\Models\OpenDoor;
use Carbon\Carbon;
use Faker\Factory as Faker;

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
            'term' => 1,
            'language' => 'fr',
            'campus' => 'BPR',
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'schedule' => [
                    ['day' => 'Monday', 'start' => '17:30', 'finish' => '18:30']
                ],
                'maxStudents' => 15
            ]),
            'organiser_id' => 1,
            'start_date' => (Carbon::now())->addWeek(1),
        ]);
        $w->themes()->attach([1,4]);
        $w->createExitSurvey();

        $w = Workshop::create([
            'title_fr' => 'Introduction au Mahjong',
            'title_en' => 'Intro to Mahjong',
            'description' => json_encode([
                'fr' => 'Lancez-vous dans ce jeu ancestral chinois où se mèlent stratégie et tradition',
                'en' => 'Play this ancestral chinese game where strategy meets tradition'
            ]),
            'term' => 1,
            'language' => 'both',
            'campus' => 'BPR',
            'details' => json_encode([
                'nbSessions' => 6,
                'roomNb' => 'π (314 BPR)',
                'schedule' => [
                    ['day' => 'Tuesday', 'start' => '12:30', 'finish' => '13:30']
                ],
                'maxStudents' => 12
            ]),
            'organiser_id' => 2,
            'start_date' => (Carbon::now())->addWeek(1),
        ]);
        $w->themes()->attach([4,5]);
        $w->createExitSurvey();

        $faker = Faker::create();
        $locations = [
            ['campus' => 'BPR', 'room' => 'π (314 BPR)'], ['campus' => 'TKO', 'room' => 'S302']
        ];
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        for($i=0 ; $i <= 10 ; $i++){
            $nb = rand(0, 4);
            $themes = [];
            for($k=0 ; $k <= $nb ; $k++){
                $themes[] = rand(1, 8);
            }
            $location = $locations[rand(0,1)];
            $time = rand(8, 15);

            $w = Workshop::create([
            'title_fr' => '',
            'title_en' => $faker->sentence(),
            'description' => json_encode([
                'fr' => '',
                'en' => $faker->paragraph()
            ]),
            'term' => 1,
            'language' => 'en',
            'campus' => $location['campus'],
            'details' => json_encode([
                'nbSessions' => rand(4, 10),
                'roomNb' => $location['room'],
                'schedule' => [
                    ['day' => $daysOfWeek[rand(0,6)], 'start' => $time.':'.['00', '30'][rand(0,1)], 'finish' => ($time+1).':'.['00', '30'][rand(0,1)]]
                ],
                'maxStudents' => rand(10, 20)
            ]),
            'organiser_id' => rand(1, 4),
            'start_date' => $faker->dateTimeBetween('2024-09-01', '2024-11-15')
        ]);
        $w->themes()->attach($themes);
        $w->createExitSurvey();
        }

        $locations = [
            ['campus' => 'BPR', 'room' => 'π (314 BPR)'], ['campus' => 'TKO', 'room' => 'S302']
        ];
        for($i=0 ; $i<=50 ; $i++){
            $location = $locations[rand(0,1)];
            $time = rand(8, 15);
            OpenDoor::create([
                'teacher_id' => rand(1, 4),
                'type' => 'Open doors',
                'date' => $faker->dateTimeBetween('2024-09-01', '2024-11-15'),
                'start' => $time.':'.['00', '30'][rand(0,1)], 
                'finish' => ($time+1).':'.['00', '30'][rand(0,1)],
                'roomNb' => $location['room'],
                'campus' => $location['campus']
            ]);
        }
    }
}
