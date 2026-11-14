<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Prefecture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prefecture>
 */
class PrefectureFactory extends Factory
{
    protected $model = Prefecture::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var array<int, string> $names */
        $names = require database_path('data/prefectures.php');
        $id = fake()->unique()->numberBetween(1, 47);

        return [
            'id' => $id,
            'name' => $names[$id],
        ];
    }
}
