<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)
        ->has(\App\Models\Post::factory()->count(10)
            ->has(\App\Models\Comment::factory()->count(15)))->create();
    }
}
