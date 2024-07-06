<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Post;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for($i=0 ; $i <= 50 ; $i++){
            $title = $faker->sentence();
            $post = $faker->paragraph();
            $nb = rand(3, 8);
            for($k=0 ; $k <= $nb ; $k++){
                $post .= '<br/>'.$faker->paragraph();
            }
            $status = ['draft', 'submitted', 'published'][rand(0,2)];
            // $randomDate = $faker->dateTimeBetween('2020-01-01', '2023-12-31');
            Post::create([
                'author_id' => rand(1, 4),
                'title' => $title,
                'slug' => Str::slug($title),
                'language' => ['fr', 'en'][rand(0,1)],
                'description' => $faker->paragraph(),
                'images' => json_encode(['cover' => ['url' => '/images/post default cover.png', 'titleColor' => '#000000'], 'post' => []]),
                'post' => $post,
                'status' => $status,
                'published_at' => $status == 'published' ? $faker->dateTimeBetween('2023-01-01', '2024-06-31'): null
            ]);
        }
    }
}
