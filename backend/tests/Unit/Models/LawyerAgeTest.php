<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Lawyer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LawyerAgeTest extends TestCase
{
    /**
     * @return array<string, array{string, string, int}>
     */
    public static function ageProvider(): array
    {
        return [
            '誕生日の前日' => ['1990-05-10', '2026-05-09', 35],
            '誕生日の当日' => ['1990-05-10', '2026-05-10', 36],
            '誕生日の翌日' => ['1990-05-10', '2026-05-11', 36],
            '年をまたぐ直前(12/31生まれの12/30)' => ['1990-12-31', '2026-12-30', 35],
            '年をまたぐ当日(1/1生まれの1/1)' => ['1990-01-01', '2026-01-01', 36],
        ];
    }

    #[DataProvider('ageProvider')]
    public function test_age_誕生日前後_年齢が正しく切り替わる(string $birthDate, string $today, int $expected): void
    {
        $this->travelTo($today.' 00:00:00');

        $lawyer = new Lawyer(['birth_date' => $birthDate]);

        $this->assertSame($expected, $lawyer->age);
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function leapDayProvider(): array
    {
        return [
            'うるう年以外の2月28日' => ['2026-02-28', 25],
            'うるう年以外の3月1日' => ['2026-03-01', 26],
            'うるう年の2月28日' => ['2028-02-28', 27],
            'うるう年の2月29日' => ['2028-02-29', 28],
        ];
    }

    #[DataProvider('leapDayProvider')]
    public function test_age_2月29日生まれ_うるう年以外は3月1日に加算する(string $today, int $expected): void
    {
        $this->travelTo($today.' 12:00:00');

        $lawyer = new Lawyer(['birth_date' => '2000-02-29']);

        $this->assertSame($expected, $lawyer->age);
    }

    public function test_age_日本時間の0時直後_日本の日付で判定する(): void
    {
        // UTC では前日(5/9 15:00)だが、日本時間では誕生日当日
        $this->travelTo('2026-05-10 00:00:00');

        $lawyer = new Lawyer(['birth_date' => '1990-05-10']);

        $this->assertSame(36, $lawyer->age);
    }
}
