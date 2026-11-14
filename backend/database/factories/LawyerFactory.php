<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LawOffice;
use App\Models\Lawyer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lawyer>
 */
class LawyerFactory extends Factory
{
    protected $model = Lawyer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = CarbonImmutable::today();
        // 25〜80歳
        $birthDate = CarbonImmutable::instance(
            fake()->dateTimeBetween($today->subYears(81)->addDay(), $today->subYears(25))
        );
        // 登録年は25歳になる年以降
        $registeredYear = fake()->numberBetween($birthDate->year + 25, $today->year);

        /** @var array{last: list<array{string, string}>, first: list<array{string, string}>} $names */
        $names = require database_path('data/names.php');
        [$lastName, $lastKana] = fake()->randomElement($names['last']);
        [$firstName, $firstKana] = fake()->randomElement($names['first']);

        return [
            'law_office_id' => LawOffice::factory(),
            'name' => "{$lastName} {$firstName}",
            'name_kana' => "{$lastKana} {$firstKana}",
            'birth_date' => $birthDate->toDateString(),
            'registered_year' => $registeredYear,
            'case_count' => fake()->numberBetween(0, 500),
            'career' => "{$registeredYear}年 弁護士登録",
            'introduction' => fake()->realText(120),
        ];
    }

    public function bornOn(string $date): static
    {
        return $this->state(['birth_date' => $date]);
    }
}
