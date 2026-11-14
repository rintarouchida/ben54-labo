<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * 検索結果のページ(要件定義書 5.2節の data / links / meta)
 */
class LawyerCollection extends ResourceCollection
{
    public $collects = LawyerResource::class;

    /**
     * Laravel 標準の meta から path と links(ページ番号ボタン用の配列)を除き、5.2節の項目だけにする。
     *
     * @param  array<string, mixed>  $paginated
     * @param  array{links: array<string, ?string>, meta: array<string, mixed>}  $default
     * @return array{links: array<string, ?string>, meta: array<string, mixed>}
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'links' => $default['links'],
            'meta' => array_intersect_key(
                $default['meta'],
                array_flip(['current_page', 'last_page', 'per_page', 'total', 'from', 'to']),
            ),
        ];
    }
}
