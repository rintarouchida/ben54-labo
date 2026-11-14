<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * API-01 の並び順(要件定義書 5.1節)
 */
enum LawyerSortType: string
{
    /** 取扱裁判実績数の多い順(初期値) */
    case CaseCount = 'case_count';

    /** 年齢の高い順(生年月日の古い順) */
    case Age = 'age';

    /** フリガナの五十音順 */
    case Name = 'name';

    /**
     * ORDER BY に渡すカラムと向き。同値のときの並びを安定させるため、最後に必ず id ASC を付ける。
     *
     * @return array<string, 'asc'|'desc'>
     */
    public function orderColumns(): array
    {
        $primary = match ($this) {
            self::CaseCount => ['case_count' => 'desc'],
            self::Age => ['birth_date' => 'asc'],
            self::Name => ['name_kana' => 'asc'],
        };

        return $primary + ['id' => 'asc'];
    }
}
