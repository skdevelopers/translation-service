<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding 100,000 translations...');

        $start = microtime(true);

        Translation::factory()
            ->count(100000)
            ->make()
            ->chunk(5000)
            ->each(function ($chunk) {
                Translation::insert($chunk->toArray());
            });

        $this->command->info('Seeded in ' . round(microtime(true) - $start, 2) . 's');
    }
}
