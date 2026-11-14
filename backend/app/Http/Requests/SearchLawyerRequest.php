<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LawyerSortType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API-01 のリクエストパラメータ(要件定義書 5.1節)
 */
class SearchLawyerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 空のクエリ(`?keyword=` など)は ConvertEmptyStringsToNull で null になるため、nullable を付けて「指定なし」として通す。
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'prefecture_id' => ['bail', 'nullable', 'integer', 'exists:prefectures,id'],
            'practice_area_ids' => ['nullable', 'array', 'max:12'],
            'practice_area_ids.*' => ['bail', 'integer', 'distinct', 'exists:practice_areas,id'],
            'keyword' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::enum(LawyerSortType::class)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function prefectureId(): ?int
    {
        $value = $this->validated('prefecture_id');

        return $value === null ? null : (int) $value;
    }

    /**
     * @return list<int>
     */
    public function practiceAreaIds(): array
    {
        /** @var array<int|string>|null $values */
        $values = $this->validated('practice_area_ids');

        return array_values(array_map(intval(...), $values ?? []));
    }

    public function keyword(): ?string
    {
        /** @var string|null */
        return $this->validated('keyword');
    }

    /**
     * 省略時は取扱裁判実績数順
     */
    public function sortType(): LawyerSortType
    {
        /** @var string|null $value */
        $value = $this->validated('sort');

        return LawyerSortType::tryFrom($value ?? '') ?? LawyerSortType::CaseCount;
    }

    public function page(): int
    {
        $value = $this->validated('page');

        return $value === null ? 1 : (int) $value;
    }
}
