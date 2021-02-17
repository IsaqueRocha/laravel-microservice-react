<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        Genre::factory(100)
            ->create()
            ->each(function ($genre) use ($categories) {
                $categoriesID = $categories->random(5)->pluck('id')->toArray();
                $genre->categories()->attach($categoriesID);
            });
    }
}
