<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchLawyerRequest;
use App\Http\Resources\LawyerCollection;
use App\Http\Resources\LawyerDetailResource;
use App\Services\LawyerSearchService;

class LawyerController extends Controller
{
    /**
     * API-01 弁護士を検索し、20件ずつ返す
     */
    public function index(SearchLawyerRequest $request, LawyerSearchService $service): LawyerCollection
    {
        $lawyers = $service->search(
            prefectureId: $request->prefectureId(),
            practiceAreaIds: $request->practiceAreaIds(),
            keyword: $request->keyword(),
            sort: $request->sortType(),
            page: $request->page(),
        );

        // links の URL に検索条件とソートを引き継ぐ
        return new LawyerCollection($lawyers->withQueryString());
    }

    /**
     * API-02 弁護士1件の詳細を返す。存在しない ID は 404(bootstrap/app.php で変換)
     */
    public function show(int $id, LawyerSearchService $service): LawyerDetailResource
    {
        return new LawyerDetailResource($service->find($id));
    }
}
