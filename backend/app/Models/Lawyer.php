<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\LikeEscaper;
use Carbon\CarbonImmutable;
use Database\Factories\LawyerFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property CarbonImmutable $birth_date
 * @property-read int $age
 */
class Lawyer extends Model
{
    /** @use HasFactory<LawyerFactory> */
    use HasFactory;

    protected $fillable = [
        'law_office_id',
        'name',
        'name_kana',
        'birth_date',
        'registered_year',
        'case_count',
        'career',
        'introduction',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'registered_year' => 'integer',
            'case_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<LawOffice, $this>
     */
    public function lawOffice(): BelongsTo
    {
        return $this->belongsTo(LawOffice::class);
    }

    /**
     * @return BelongsToMany<PracticeArea, $this>
     */
    public function practiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(PracticeArea::class);
    }

    /**
     * 生年月日から算出した満年齢(保存はしない)。誕生日の当日に1歳加算する。
     * 2月29日生まれは、うるう年以外では3月1日に加算する。
     *
     * @return Attribute<int, never>
     */
    protected function age(): Attribute
    {
        return Attribute::get(function (): int {
            $today = CarbonImmutable::today();
            $birth = $this->birth_date;

            $age = $today->year - $birth->year;
            $hadBirthday = [$today->month, $today->day] >= [$birth->month, $birth->day];

            return $hadBirthday ? $age : $age - 1;
        });
    }

    /**
     * 所属事務所の所在地で都道府県を絞り込む。null なら絞り込まない。
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function inPrefecture(Builder $query, ?int $prefectureId): void
    {
        if ($prefectureId === null) {
            return;
        }

        $query->whereHas('lawOffice', function (Builder $office) use ($prefectureId): void {
            $office->where('prefecture_id', $prefectureId);
        });
    }

    /**
     * 指定した分野のいずれかを扱う弁護士に絞り込む(OR)。EXISTS サブクエリのため結果は重複しない。
     * null または空配列なら絞り込まない。
     *
     * @param  Builder<self>  $query
     * @param  list<int>|null  $practiceAreaIds
     */
    #[Scope]
    protected function withAnyPracticeArea(Builder $query, ?array $practiceAreaIds): void
    {
        if ($practiceAreaIds === null || $practiceAreaIds === []) {
            return;
        }

        $query->whereHas('practiceAreas', function (Builder $area) use ($practiceAreaIds): void {
            $area->whereIn('lawyer_practice_area.practice_area_id', $practiceAreaIds);
        });
    }

    /**
     * 名前・フリガナ・事務所名・自己紹介を部分一致で検索する。
     * 半角・全角の空白で区切った複数語は AND。null・空文字・空白のみなら絞り込まない。
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function matchKeyword(Builder $query, ?string $keyword): void
    {
        if ($keyword === null) {
            return;
        }

        $words = preg_split('/[\s\x{3000}]+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            $pattern = '%'.LikeEscaper::escapeLike($word).'%';

            $query->where(function (Builder $q) use ($pattern): void {
                $q->where('name', 'like', $pattern)
                    ->orWhere('name_kana', 'like', $pattern)
                    ->orWhere('introduction', 'like', $pattern)
                    ->orWhereHas('lawOffice', function (Builder $office) use ($pattern): void {
                        $office->where('name', 'like', $pattern);
                    });
            });
        }
    }
}
