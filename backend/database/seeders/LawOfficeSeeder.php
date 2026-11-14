<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LawOffice;
use App\Models\Prefecture;
use Illuminate\Database\Seeder;

class LawOfficeSeeder extends Seeder
{
    public const COUNT = 300;

    public function run(): void
    {
        /** @var array<int, array{int, string}> $profiles */
        $profiles = require database_path('data/prefecture_profiles.php');
        $prefectures = Prefecture::query()->pluck('name', 'id');

        for ($i = 0; $i < self::COUNT; $i++) {
            $prefectureId = $this->pickPrefectureByPopulation($profiles);
            [, $city] = $profiles[$prefectureId];

            LawOffice::factory()->create([
                'prefecture_id' => $prefectureId,
                'address' => $prefectures[$prefectureId].$city.fake()->streetAddress(),
            ]);
        }
    }

    /**
     * 人口を重みにして都道府県を1つ選ぶ(東京・大阪・愛知などが多くなる)
     *
     * @param  array<int, array{int, string}>  $profiles
     */
    private function pickPrefectureByPopulation(array $profiles): int
    {
        $total = array_sum(array_column($profiles, 0));
        $point = fake()->numberBetween(1, $total);

        foreach ($profiles as $id => [$population]) {
            $point -= $population;
            if ($point <= 0) {
                return $id;
            }
        }

        return array_key_last($profiles);
    }
}
