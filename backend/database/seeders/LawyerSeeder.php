<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LawyerSeeder extends Seeder
{
    public const COUNT = 1000;

    public function run(): void
    {
        $officeIds = LawOffice::query()->pluck('id')->all();
        $practiceAreaIds = PracticeArea::query()->pluck('id')->all();

        $lawyers = Lawyer::factory()
            ->count(self::COUNT)
            ->state(fn (): array => ['law_office_id' => fake()->randomElement($officeIds)])
            ->create();

        // 1人あたり1〜4分野をランダムに割り当てる
        $rows = [];
        foreach ($lawyers as $lawyer) {
            $areaIds = fake()->randomElements($practiceAreaIds, fake()->numberBetween(1, 4));
            foreach ($areaIds as $areaId) {
                $rows[] = ['lawyer_id' => $lawyer->id, 'practice_area_id' => $areaId];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('lawyer_practice_area')->insert($chunk);
        }
    }
}
