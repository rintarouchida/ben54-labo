<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PracticeArea;
use Illuminate\Database\Seeder;

class PracticeAreaSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<string> $names */
        $names = require database_path('data/practice_areas.php');
        $now = now();

        PracticeArea::insert(array_map(
            fn (string $name): array => ['name' => $name, 'created_at' => $now, 'updated_at' => $now],
            $names,
        ));
    }
}
