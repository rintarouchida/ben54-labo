<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PracticeArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeArea>
 */
class PracticeAreaFactory extends Factory
{
    protected $model = PracticeArea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var list<string> $names */
        $names = require database_path('data/practice_areas.php');

        return [
            'name' => fake()->unique()->randomElement($names),
        ];
    }
}
