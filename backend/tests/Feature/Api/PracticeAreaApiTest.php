<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\PracticeArea;
use Database\Seeders\PracticeAreaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeAreaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_practiceAreas_一覧を取得する_登録件数分をID昇順で返す(): void
    {
        // 登録順と ID 順が逆になるようにする
        PracticeArea::factory()->create(['id' => 3, 'name' => '交通事故']);
        PracticeArea::factory()->create(['id' => 1, 'name' => '離婚・男女問題']);
        PracticeArea::factory()->create(['id' => 2, 'name' => '相続']);

        $this->getJson('/api/v1/practice-areas')
            ->assertOk()
            ->assertExactJson(['data' => [
                ['id' => 1, 'name' => '離婚・男女問題'],
                ['id' => 2, 'name' => '相続'],
                ['id' => 3, 'name' => '交通事故'],
            ]]);
    }

    public function test_practiceAreas_Seeder投入後に取得する_12分野を返す(): void
    {
        $this->seed(PracticeAreaSeeder::class);

        $this->getJson('/api/v1/practice-areas')
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('data.0.name', '離婚・男女問題')
            ->assertJsonPath('data.11.name', '外国人問題');
    }

    public function test_practiceAreas_マスターが0件_空配列を返す(): void
    {
        $this->getJson('/api/v1/practice-areas')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }
}
