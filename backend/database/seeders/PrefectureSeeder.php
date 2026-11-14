<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Prefecture;
use Illuminate\Database\Seeder;

class PrefectureSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, string> $names */
        $names = require database_path('data/prefectures.php');
        $now = now();

        Prefecture::insert(array_map(
            fn (int $id, string $name): array => ['id' => $id, 'name' => $name, 'created_at' => $now, 'updated_at' => $now],
            array_keys($names),
            $names,
        ));
    }
}
