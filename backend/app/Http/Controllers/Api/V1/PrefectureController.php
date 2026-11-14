<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrefectureResource;
use App\Models\Prefecture;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PrefectureController extends Controller
{
    /**
     * API-03 都道府県の一覧を id の昇順で返す
     */
    public function index(): AnonymousResourceCollection
    {
        return PrefectureResource::collection(Prefecture::query()->orderBy('id')->get());
    }
}
