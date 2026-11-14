<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // 何度投入しても同じデータになるよう、Faker(mt_rand)のシード値を固定する
    public const FAKER_SEED = 20261114;

    public function run(): void
    {
        fake()->seed(self::FAKER_SEED);

        $this->call([
            PrefectureSeeder::class,
            PracticeAreaSeeder::class,
            LawOfficeSeeder::class,
            LawyerSeeder::class,
        ]);
    }
}
