<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use App\Models\Prefecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LawyerDetailApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_存在するID_5_3節の全項目を返す(): void
    {
        $this->travelTo('2026-04-12');
        $tokyo = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $office = LawOffice::factory()->for($tokyo)->create([
            'name' => '山田総合法律事務所',
            'address' => '東京都千代田区丸の内1-1-1',
            'phone_number' => '03-1234-5678',
        ]);
        [$divorce, $inheritance] = PracticeArea::factory()->count(2)->create();
        $lawyer = Lawyer::factory()->for($office)->hasAttached([$inheritance, $divorce])->create([
            'name' => '山田 太郎',
            'name_kana' => 'ヤマダ タロウ',
            'birth_date' => '1974-04-12',
            'registered_year' => 2001,
            'case_count' => 340,
            'career' => '2001年 弁護士登録',
            'introduction' => '相続と離婚を中心に扱っています。',
        ]);

        $this->getJson("/api/v1/lawyers/{$lawyer->id}")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $lawyer->id,
                    'name' => '山田 太郎',
                    'name_kana' => 'ヤマダ タロウ',
                    'birth_date' => '1974-04-12',
                    'age' => 52,
                    'registered_year' => 2001,
                    'case_count' => 340,
                    'career' => '2001年 弁護士登録',
                    'introduction' => '相続と離婚を中心に扱っています。',
                    'law_office' => [
                        'id' => $office->id,
                        'name' => '山田総合法律事務所',
                        'address' => '東京都千代田区丸の内1-1-1',
                        'phone_number' => '03-1234-5678',
                    ],
                    'prefecture' => ['id' => 13, 'name' => '東京都'],
                    // 分野は ID 昇順
                    'practice_areas' => [
                        ['id' => $divorce->id, 'name' => $divorce->name],
                        ['id' => $inheritance->id, 'name' => $inheritance->name],
                    ],
                ],
            ]);
    }

    public function test_show_分野を持たない弁護士_空配列を返す(): void
    {
        $lawyer = Lawyer::factory()->create();

        $this->getJson("/api/v1/lawyers/{$lawyer->id}")
            ->assertOk()
            ->assertJsonPath('data.practice_areas', []);
    }

    public function test_show_存在しないID_404と日本語のmessageを返す(): void
    {
        $lawyer = Lawyer::factory()->create();

        $this->getJson('/api/v1/lawyers/'.($lawyer->id + 1))
            ->assertNotFound()
            ->assertExactJson(['message' => '弁護士が見つかりません。']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidIdProvider(): array
    {
        return [
            '文字列' => ['abc'],
            '数字と文字の混在' => ['1a'],
            '小数' => ['1.5'],
            '負数' => ['-1'],
            '0' => ['0'],
            '先頭が0' => ['01'],
            'intに収まらない桁数' => ['99999999999999999999'],
        ];
    }

    #[DataProvider('invalidIdProvider')]
    public function test_show_数値でないID_404と日本語のmessageを返す(string $id): void
    {
        Lawyer::factory()->create();

        $this->getJson("/api/v1/lawyers/{$id}")
            ->assertNotFound()
            ->assertExactJson(['message' => '弁護士が見つかりません。']);
    }
}
