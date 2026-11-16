<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Services\LawyerSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * API 共通のエラーレスポンス(要件定義書 5.4節・7.4節・7.6節・8.2節の「共通」)
 */
class ApiErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'SQLSTATE[HY000] 接続先 db-secret-host:3306';

    // ---- 404 ----

    public function test_api_未定義のパス_404と日本語のmessageを返す(): void
    {
        $this->getJson('/api/v1/undefined-path')
            ->assertNotFound()
            ->assertExactJson(['message' => '弁護士が見つかりません。']);
    }

    public function test_api_未定義のパスにAcceptヘッダーなしでアクセスする_JSONの404を返す(): void
    {
        $this->get('/api/v1/undefined-path')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['message' => '弁護士が見つかりません。']);
    }

    public function test_api_v1以外の未定義のパス_JSONの404を返す(): void
    {
        $this->get('/api/undefined-path')
            ->assertNotFound()
            ->assertExactJson(['message' => '弁護士が見つかりません。']);
    }

    public function test_web_未定義のパス_APIのmessageを返さない(): void
    {
        $response = $this->get('/undefined-path');

        $response->assertNotFound();
        $this->assertStringNotContainsString('弁護士が見つかりません。', (string) $response->getContent());
    }

    // ---- 422 ----

    public function test_api_バリデーションエラー_日本語のmessageとerrorsを返す(): void
    {
        $this->getJson('/api/v1/lawyers?keyword='.str_repeat('あ', 101))
            ->assertUnprocessable()
            ->assertExactJson([
                'message' => 'キーワードは100文字以内で入力してください。',
                'errors' => ['keyword' => ['キーワードは100文字以内で入力してください。']],
            ]);
    }

    public function test_api_複数項目のバリデーションエラー_各項目名を日本語で返しmessageに他の件数を付ける(): void
    {
        $response = $this->getJson('/api/v1/lawyers?prefecture_id=abc&practice_area_ids=1&sort=price&page=0');

        $response->assertUnprocessable()
            ->assertJsonPath('message', '都道府県は整数で指定してください。 (他3件のエラー)')
            ->assertJsonPath('errors.prefecture_id', ['都道府県は整数で指定してください。'])
            ->assertJsonPath('errors.practice_area_ids', ['取扱分野は配列で指定してください。'])
            ->assertJsonPath('errors.sort', ['選択された並び順は正しくありません。'])
            ->assertJsonPath('errors.page', ['ページは1以上の値を指定してください。']);
    }

    public function test_api_取扱分野の要素が不正_要素の項目名も日本語で返す(): void
    {
        $this->getJson('/api/v1/lawyers?practice_area_ids[]=abc')
            ->assertUnprocessable()
            ->assertJsonPath('errors', ['practice_area_ids.0' => ['取扱分野は整数で指定してください。']]);
    }

    // ---- 429 ----

    public function test_api_1分間に61回リクエストする_61回目が429(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/api/v1/prefectures')->assertOk();
        }

        $this->getJson('/api/v1/prefectures')
            ->assertTooManyRequests()
            ->assertExactJson(['message' => 'Too Many Attempts.'])
            ->assertHeader('Retry-After');
    }

    public function test_api_APP_DEBUGがtrueで61回目のリクエスト_429にスタックトレースを含まない(): void
    {
        config(['app.debug' => true]);
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/api/v1/prefectures')->assertOk();
        }

        $this->getJson('/api/v1/prefectures')
            ->assertTooManyRequests()
            ->assertExactJson(['message' => 'Too Many Attempts.']);
    }

    public function test_api_別のAPIへのリクエスト_同じIPの回数として数える(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/api/v1/prefectures')->assertOk();
        }

        $this->getJson('/api/v1/practice-areas')->assertTooManyRequests();
    }

    public function test_api_制限に達したIPと別のIP_別のIPは制限されない(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/api/v1/prefectures')->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->getJson('/api/v1/prefectures')
            ->assertOk();
    }

    public function test_api_1分経過する_再びリクエストできる(): void
    {
        $this->freezeTime();
        for ($i = 1; $i <= 60; $i++) {
            $this->getJson('/api/v1/prefectures')->assertOk();
        }
        $this->getJson('/api/v1/prefectures')->assertTooManyRequests();

        $this->travel(61)->seconds();

        $this->getJson('/api/v1/prefectures')->assertOk();
    }

    // ---- その他の HTTP 例外 ----

    public function test_api_許可されていないメソッド_APP_DEBUGがtrueでもmessageだけの405を返す(): void
    {
        config(['app.debug' => true]);

        $response = $this->postJson('/api/v1/lawyers');

        $response->assertMethodNotAllowed()
            ->assertHeader('Allow', 'GET, HEAD')
            ->assertJsonStructure(['message']);
        $this->assertSame(['message'], array_keys((array) $response->json()));
    }

    // ---- 500 ----

    public function test_api_予期しない例外_APP_DEBUGがfalseでスタックトレースを含まない500を返す(): void
    {
        config(['app.debug' => false]);
        $this->mockSearchServiceToThrow();

        $response = $this->getJson('/api/v1/lawyers');

        $response->assertInternalServerError()
            ->assertExactJson(['message' => 'サーバーエラーが発生しました。']);
        $this->assertResponseHasNoDebugInfo((string) $response->getContent());
    }

    public function test_api_予期しない例外_APP_DEBUGがtrueでもスタックトレースを含まない500を返す(): void
    {
        config(['app.debug' => true]);
        $this->mockSearchServiceToThrow();

        $response = $this->getJson('/api/v1/lawyers');

        $response->assertInternalServerError()
            ->assertExactJson(['message' => 'サーバーエラーが発生しました。']);
        $this->assertResponseHasNoDebugInfo((string) $response->getContent());
    }

    public function test_api_予期しない例外_例外をログに記録する(): void
    {
        Exceptions::fake();
        $this->mockSearchServiceToThrow();

        $this->getJson('/api/v1/lawyers')->assertInternalServerError();

        Exceptions::assertReported(
            fn (RuntimeException $e): bool => $e->getMessage() === self::SECRET,
        );
    }

    public function test_api_404や422_例外としてログに記録しない(): void
    {
        Exceptions::fake();

        $this->getJson('/api/v1/undefined-path')->assertNotFound();
        $this->getJson('/api/v1/lawyers?page=0')->assertUnprocessable();

        Exceptions::assertNothingReported();
    }

    private function mockSearchServiceToThrow(): void
    {
        $this->mock(LawyerSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')->andThrow(new RuntimeException(self::SECRET));
        });
    }

    private function assertResponseHasNoDebugInfo(string $content): void
    {
        foreach (['trace', 'exception', 'file', 'line', self::SECRET, 'LawyerSearchService'] as $needle) {
            $this->assertStringNotContainsString($needle, $content);
        }
    }
}
