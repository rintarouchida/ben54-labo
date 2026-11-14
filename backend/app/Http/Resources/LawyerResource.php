<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Lawyer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 検索結果一覧の1件(要件定義書 5.2節)。lawOffice.prefecture と practiceAreas は事前に読み込んでおくこと。
 *
 * @mixin Lawyer
 */
class LawyerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_kana' => $this->name_kana,
            'age' => $this->age,
            'case_count' => $this->case_count,
            'law_office' => [
                'id' => $this->lawOffice->id,
                'name' => $this->lawOffice->name,
            ],
            'prefecture' => new PrefectureResource($this->lawOffice->prefecture),
            'practice_areas' => PracticeAreaResource::collection($this->practiceAreas),
        ];
    }
}
