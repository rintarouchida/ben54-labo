<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Lawyer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 弁護士詳細(要件定義書 5.3節)。lawOffice.prefecture と practiceAreas は事前に読み込んでおくこと。
 *
 * @mixin Lawyer
 */
class LawyerDetailResource extends JsonResource
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
            'birth_date' => $this->birth_date->toDateString(),
            'age' => $this->age,
            'registered_year' => $this->registered_year,
            'case_count' => $this->case_count,
            'career' => $this->career,
            'introduction' => $this->introduction,
            'law_office' => [
                'id' => $this->lawOffice->id,
                'name' => $this->lawOffice->name,
                'address' => $this->lawOffice->address,
                'phone_number' => $this->lawOffice->phone_number,
            ],
            'prefecture' => new PrefectureResource($this->lawOffice->prefecture),
            'practice_areas' => PracticeAreaResource::collection($this->practiceAreas),
        ];
    }
}
