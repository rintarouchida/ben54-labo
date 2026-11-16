<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\LawyerSortType;
use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use App\Models\Prefecture;
use App\Services\LawyerSearchService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private LawyerSearchService $service;

    private PracticeArea $divorce;

    private PracticeArea $traffic;

    /**
     * 東京・大阪 × 離婚・交通事故 × 自己紹介「相続」の有無 で8人を作る
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LawyerSearchService;
        $tokyo = LawOffice::factory()->for(Prefecture::factory()->state(['id' => 13, 'name' => '東京都']))->create();
        $osaka = LawOffice::factory()->for(Prefecture::factory()->state(['id' => 27, 'name' => '大阪府']))->create();
        [$this->divorce, $this->traffic] = PracticeArea::factory()->count(2)->create();

        foreach ([$tokyo, $osaka] as $office) {
            foreach ([$this->divorce, $this->traffic] as $area) {
                foreach (['相続を扱います。', '労働問題を扱います。'] as $introduction) {
                    Lawyer::factory()->for($office)->hasAttached($area)->create([
                        'name' => '佐藤 花子',
                        'name_kana' => 'サトウ ハナコ',
                        'introduction' => $introduction,
                    ]);
                }
            }
        }
    }

    public function test_search_条件なし_全件を返す(): void
    {
        $this->assertSame(8, $this->service->search()->total());
    }

    public function test_search_都道府県だけ_その県の件数を返す(): void
    {
        $this->assertSame(4, $this->service->search(prefectureId: 13)->total());
    }

    public function test_search_分野だけ_その分野の件数を返す(): void
    {
        $this->assertSame(4, $this->service->search(practiceAreaIds: [$this->divorce->id])->total());
    }

    public function test_search_分野を2つ_どちらかを扱う件数を返す(): void
    {
        $this->assertSame(8, $this->service->search(practiceAreaIds: [$this->divorce->id, $this->traffic->id])->total());
    }

    public function test_search_キーワードだけ_含む件数を返す(): void
    {
        $this->assertSame(4, $this->service->search(keyword: '相続')->total());
    }

    public function test_search_都道府県と分野_両方を満たす件数を返す(): void
    {
        $this->assertSame(2, $this->service->search(prefectureId: 27, practiceAreaIds: [$this->traffic->id])->total());
    }

    public function test_search_3条件_すべてを満たす件数を返す(): void
    {
        $result = $this->service->search(prefectureId: 13, practiceAreaIds: [$this->divorce->id], keyword: '相続');

        $this->assertSame(1, $result->total());
    }

    public function test_search_空文字のキーワード_条件なしとして扱う(): void
    {
        $this->assertSame(8, $this->service->search(keyword: '')->total());
    }

    public function test_search_空白のみのキーワード_条件なしとして扱う(): void
    {
        $this->assertSame(8, $this->service->search(keyword: " \u{3000} ")->total());
    }

    public function test_search_空配列の分野_条件なしとして扱う(): void
    {
        $this->assertSame(8, $this->service->search(practiceAreaIds: [])->total());
    }

    public function test_search_ソートを指定_指定した順で並べる(): void
    {
        Lawyer::query()->orderBy('id')->get()->each(function (Lawyer $lawyer, int $i): void {
            // 2人ずつ同じ実績数にする: 0,0,1,1,2,2,3,3
            $lawyer->update(['case_count' => intdiv($i, 2)]);
        });
        $ids = Lawyer::query()->orderBy('id')->pluck('id')->all();

        $result = $this->service->search(sort: LawyerSortType::CaseCount);

        $this->assertSame([$ids[6], $ids[7], $ids[4], $ids[5], $ids[2], $ids[3], $ids[0], $ids[1]], $result->modelKeys());
    }

    public function test_search_関連データ_事前に読み込む(): void
    {
        $lawyer = $this->service->search()->first();

        $this->assertTrue($lawyer->relationLoaded('lawOffice'));
        $this->assertTrue($lawyer->lawOffice->relationLoaded('prefecture'));
        $this->assertTrue($lawyer->relationLoaded('practiceAreas'));
    }

    public function test_search_最終ページを超えるページ_空のページを返す(): void
    {
        $result = $this->service->search(page: 2);

        $this->assertSame([], $result->items());
        $this->assertSame(8, $result->total());
        $this->assertSame(2, $result->currentPage());
    }

    public function test_find_存在するID_関連データ込みで返す(): void
    {
        $id = Lawyer::query()->orderBy('id')->value('id');

        $lawyer = $this->service->find($id);

        $this->assertSame($id, $lawyer->id);
        $this->assertTrue($lawyer->relationLoaded('lawOffice'));
        $this->assertTrue($lawyer->lawOffice->relationLoaded('prefecture'));
        $this->assertTrue($lawyer->relationLoaded('practiceAreas'));
    }

    public function test_find_存在しないID_ModelNotFoundExceptionを投げる(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->find((int) Lawyer::query()->max('id') + 1);
    }

    public function test_search_1ページの件数_20件で区切る(): void
    {
        $this->assertSame(LawyerSearchService::PER_PAGE, $this->service->search()->perPage());
        $this->assertSame(20, LawyerSearchService::PER_PAGE);
    }
}
