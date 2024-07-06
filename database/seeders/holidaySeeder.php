<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Holiday;
use App\Models\Term;

class holidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            ['name' => 'Mid-Autumn Festival', 'start' => '2024/09/18', 'finish' => '2024/09/18'],
            ['name' => 'National Day', 'start' => '2024/09/30', 'finish' => '2024/10/01'],
            ['name' => 'Chung Yeung Festival', 'start' => '2024/10/11', 'finish' => '2024/10/11'],
            ['name' => 'October Break', 'start' => '2024/10/28', 'finish' => '2024/11/03'],
            ['name' => 'Winter Break', 'start' => '2024/12/23', 'finish' => '2025/01/05'],
            ['name' => 'Chinese New Year', 'start' => '2025/01/27', 'finish' => '2025/02/03'],
            ['name' => 'March Break', 'start' => '2025/03/10', 'finish' => '2025/03/16'],
            ['name' => 'Ching Ming Festival', 'start' => '2025/04/04', 'finish' => '2025/04/04'],
            ['name' => 'Spring Break', 'start' => '2025/04/18', 'finish' => '2025/05/05']
        ];
        foreach($holidays as $holiday){
            Holiday::create($holiday);
        }

        $terms = [
            ['nb' => 1, 'start_date' => '2024-09-02', 'finish_date' => '2024-11-15'],
            ['nb' => 2, 'start_date' => '2024-11-18', 'finish_date' => '2025-03-07'],
            ['nb' => 3, 'start_date' => '2025-03-17', 'finish_date' => '2025-06-27']
        ];
        foreach($terms as $term){
            Term::create($term);
        }
    }
}
