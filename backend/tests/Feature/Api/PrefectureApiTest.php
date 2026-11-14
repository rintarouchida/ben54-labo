<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Prefecture;
use Database\Seeders\PrefectureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrefectureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefectures_一覧を取得する_47件をID昇順で返す(): void
    {
        // Factory はランダムな順で ID を作るため、登録順と ID 順が一致しない
        Prefecture::factory()->count(47)->create();

        $response = $this->getJson('/api/v1/prefectures');

        $response->assertOk()
            ->assertJsonCount(47, 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]]);
        $this->assertSame(range(1, 47), array_column($response->json('data'), 'id'));
    }

    public function test_prefectures_Seeder投入後に取得する_JISコードと名前の組を返す(): void
    {
        $this->seed(PrefectureSeeder::class);

        $response = $this->getJson('/api/v1/prefectures');

        $response->assertOk()
            ->assertJsonPath('data.0', ['id' => 1, 'name' => '北海道'])
            ->assertJsonPath('data.12', ['id' => 13, 'name' => '東京都'])
            ->assertJsonPath('data.46', ['id' => 47, 'name' => '沖縄県']);
    }

    public function test_prefectures_マスターが0件_空配列を返す(): void
    {
        $this->getJson('/api/v1/prefectures')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }
}
