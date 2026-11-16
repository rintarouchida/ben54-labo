<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LawyerSortType;
use App\Models\Lawyer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $query = $this->queryWithRelations()
            ->inPrefecture($prefectureId)
            ->withAnyPracticeArea($practiceAreaIds)
            ->matchKeyword($keyword);

        foreach ($sort->orderColumns() as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query->paginate(perPage: self::PER_PAGE, page: $page);
    }

    /**
     * 弁護士1件を関連データ込みで返す
     *
     * @throws ModelNotFoundException<Lawyer> 存在しない ID のとき(404 に変換する)
     */
    public function find(int $id): Lawyer
    {
        return $this->queryWithRelations()->findOrFail($id);
    }

    /**
     * 一覧・詳細で表示する関連(事務所・都道府県・取扱分野)を事前に読み込む。分野は ID 昇順。
     *
     * @return Builder<Lawyer>
     */
    private function queryWithRelations(): Builder
    {
        return Lawyer::query()->with([
            'lawOffice.prefecture',
            'practiceAreas' => function (BelongsToMany $areas): void {
                $areas->orderBy('practice_areas.id');
            },
        ]);
    }
}
