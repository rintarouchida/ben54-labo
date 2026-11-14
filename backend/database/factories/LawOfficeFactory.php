<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LawOffice;
use App\Models\Prefecture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LawOffice>
 */
class LawOfficeFactory extends Factory
{
    protected $model = LawOffice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var array{last: list<array{string, string}>, first: list<array{string, string}>} $names */
        $names = require database_path('data/names.php');
        [$lastName] = fake()->randomElement($names['last']);

        return [
            'prefecture_id' => Prefecture::factory(),
            'name' => $lastName.fake()->randomElement(['', '総合', '綜合', '共同']).'法律事務所',
            // 住所は都道府県名から書く
            'address' => fn (array $attributes): string => Prefecture::findOrFail($attributes['prefecture_id'])->name
                .fake()->city().fake()->streetAddress(),
            'phone_number' => fake()->numerify('0#-####-####'),
        ];
    }
}
