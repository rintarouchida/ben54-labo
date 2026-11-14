<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PracticeAreaResource;
use App\Models\PracticeArea;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PracticeAreaController extends Controller
{
    /**
     * API-04 取扱分野の一覧を id の昇順で返す
     */
    public function index(): AnonymousResourceCollection
    {
        return PracticeAreaResource::collection(PracticeArea::query()->orderBy('id')->get());
    }
}
