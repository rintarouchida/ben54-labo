<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\LikeEscaper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LikeEscaperTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function escapeProvider(): array
    {
        return [
            'パーセント' => ['100%', '100\\%'],
            'アンダースコア' => ['a_b', 'a\\_b'],
            'バックスラッシュ' => ['a\\b', 'a\\\\b'],
            '複数の特殊文字' => ['%_\\', '\\%\\_\\\\'],
        ];
    }

    #[DataProvider('escapeProvider')]
    public function test_escapeLike_特殊文字を含む_エスケープして返す(string $input, string $expected): void
    {
        $this->assertSame($expected, LikeEscaper::escapeLike($input));
    }

    public function test_escapeLike_通常の文字のみ_そのまま返す(): void
    {
        $this->assertSame('山田 太郎', LikeEscaper::escapeLike('山田 太郎'));
    }

    public function test_escapeLike_空文字_空文字を返す(): void
    {
        $this->assertSame('', LikeEscaper::escapeLike(''));
    }
}
