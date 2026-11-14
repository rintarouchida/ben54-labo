<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LawyerSortType;
use App\Models\Lawyer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LawyerSearchService
{
    public const PER_PAGE = 20;

    /**
     * 都道府県・取扱分野(OR)・キーワードを AND で組み合わせて検索する。
     * null・空配列・空文字・空白のみの条件は「指定なし」として扱う。
     * 最終ページを超える $page は例外にせず、空のページを返す。
     *
     * @param  list<int>|null  $practiceAreaIds
     * @return LengthAwarePaginator<int, Lawyer>
     */
    public function search(
        ?int $prefectureId = null,
        ?array $practiceAreaIds = null,
        ?string $keyword = null,
        LawyerSortType $sort = LawyerSortType::CaseCount,
        int $page = 1,
    ): LengthAwarePaginator {
        $query = Lawyer::query()
            ->with([
                'lawOffice.prefecture',
                'practiceAreas' => function (BelongsToMany $areas): void {
                    $areas->orderBy('practice_areas.id');
                },
            ])
            ->inPrefecture($prefectureId)
            ->withAnyPracticeArea($practiceAreaIds)
            ->matchKeyword($keyword);

        foreach ($sort->orderColumns() as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query->paginate(perPage: self::PER_PAGE, page: $page);
    }
}
