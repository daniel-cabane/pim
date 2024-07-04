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

        for($i=0 ; $i <= 20 ; $i++){
            $title = $faker->sentence();
            $post = $faker->paragraph();
            $nb = rand(3, 8);
            for($k=0 ; $k <= $nb ; $k++){
                $post .= '<br/>'.$faker->paragraph();
            }
            Post::create([
                'author_id' => rand(1, 4),
                'title' => $title,
                'slug' => Str::slug($title),
                'language' => ['fr', 'en'][rand(0,1)],
                'description' => $faker->paragraph(),
                'post' => $post,
                'status' => ['draft', 'submitted'][rand(0,1)]
            ]);

        }
    }
}
