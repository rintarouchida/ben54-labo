<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\LawyerSortType;
use PHPUnit\Framework\TestCase;

class LawyerSortTypeTest extends TestCase
{
    public function test_orderColumns_case_count_実績数の降順とID昇順を返す(): void
    {
        $this->assertSame(['case_count' => 'desc', 'id' => 'asc'], LawyerSortType::CaseCount->orderColumns());
    }

    public function test_orderColumns_age_生年月日の昇順とID昇順を返す(): void
    {
        $this->assertSame(['birth_date' => 'asc', 'id' => 'asc'], LawyerSortType::Age->orderColumns());
    }

    public function test_orderColumns_name_フリガナの昇順とID昇順を返す(): void
    {
        $this->assertSame(['name_kana' => 'asc', 'id' => 'asc'], LawyerSortType::Name->orderColumns());
    }

    public function test_tryFrom_定義済みの文字列_対応するcaseを返す(): void
    {
        $this->assertSame(LawyerSortType::CaseCount, LawyerSortType::tryFrom('case_count'));
        $this->assertSame(LawyerSortType::Age, LawyerSortType::tryFrom('age'));
        $this->assertSame(LawyerSortType::Name, LawyerSortType::tryFrom('name'));
    }

    public function test_tryFrom_定義外の文字列_nullを返す(): void
    {
        $this->assertNull(LawyerSortType::tryFrom('price'));
        $this->assertNull(LawyerSortType::tryFrom(''));
        $this->assertNull(LawyerSortType::tryFrom('CASE_COUNT'));
    }
}
