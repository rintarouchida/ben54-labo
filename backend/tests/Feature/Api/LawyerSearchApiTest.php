<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use App\Models\Prefecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LawyerSearchApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $query
     */
    private function search(array $query = []): TestResponse
    {
        $url = '/api/v1/lawyers';
        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $this->getJson($url);
    }

    /**
     * @return list<int>
     */
    private function ids(TestResponse $response): array
    {
        return array_column($response->json('data'), 'id');
    }

    /**
     * PrefectureFactory は ID を47通りから重複なく選ぶため、件数が多いときは事務所を共有する
     */
    private function office(): LawOffice
    {
        return LawOffice::factory()->create();
    }

    // ---- 正常系: 条件 ----

    public function test_search_条件なし_20件と全件数を返す(): void
    {
        Lawyer::factory()->count(25)->for($this->office())->create();

        $this->search()
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_search_条件なし_5_2節の形式で返す(): void
    {
        $tokyo = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $office = LawOffice::factory()->for($tokyo)->create(['name' => '山田総合法律事務所']);
        [$divorce, $inheritance] = PracticeArea::factory()->count(2)->create();
        $this->travelTo('2026-04-12');
        $lawyer = Lawyer::factory()->for($office)->hasAttached([$inheritance, $divorce])->create([
            'name' => '山田 太郎',
            'name_kana' => 'ヤマダ タロウ',
            'birth_date' => '1974-04-12',
            'case_count' => 340,
        ]);

        $this->search()
            ->assertOk()
            ->assertExactJson([
                'data' => [[
                    'id' => $lawyer->id,
                    'name' => '山田 太郎',
                    'name_kana' => 'ヤマダ タロウ',
                    'age' => 52,
                    'case_count' => 340,
                    'law_office' => ['id' => $office->id, 'name' => '山田総合法律事務所'],
                    'prefecture' => ['id' => 13, 'name' => '東京都'],
                    // 分野は ID 昇順
                    'practice_areas' => [
                        ['id' => $divorce->id, 'name' => $divorce->name],
                        ['id' => $inheritance->id, 'name' => $inheritance->name],
                    ],
                ]],
                'links' => [
                    'first' => url('/api/v1/lawyers?page=1'),
                    'last' => url('/api/v1/lawyers?page=1'),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 1,
                    'from' => 1,
                    'to' => 1,
                ],
            ]);
    }

    public function test_search_都道府県を指定_その県の事務所に所属する弁護士だけを返す(): void
    {
        $tokyo = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $osaka = Prefecture::factory()->create(['id' => 27, 'name' => '大阪府']);
        $inTokyo = Lawyer::factory()->count(2)->for(LawOffice::factory()->for($tokyo))->create();
        Lawyer::factory()->for(LawOffice::factory()->for($osaka))->create();

        $response = $this->search(['prefecture_id' => 13])->assertOk();

        $this->assertEqualsCanonicalizing($inTokyo->modelKeys(), $this->ids($response));
        $response->assertJsonPath('meta.total', 2);
    }

    public function test_search_分野を2つ指定_どちらかを扱う弁護士を重複なく返す(): void
    {
        [$divorce, $inheritance, $traffic] = PracticeArea::factory()->count(3)->create();
        $office = $this->office();
        $onlyDivorce = Lawyer::factory()->for($office)->hasAttached($divorce)->create();
        $onlyInheritance = Lawyer::factory()->for($office)->hasAttached($inheritance)->create();
        $both = Lawyer::factory()->for($office)->hasAttached([$divorce, $inheritance])->create();
        Lawyer::factory()->for($office)->hasAttached($traffic)->create();

        $response = $this->search(['practice_area_ids' => [$divorce->id, $inheritance->id]])->assertOk();

        $this->assertEqualsCanonicalizing(
            [$onlyDivorce->id, $onlyInheritance->id, $both->id],
            $this->ids($response),
        );
        $response->assertJsonPath('meta.total', 3);
    }

    public function test_search_キーワード_名前_フリガナ_事務所名_自己紹介のそれぞれに含む弁護士を返す(): void
    {
        $base = ['name' => '佐藤 花子', 'name_kana' => 'サトウ ハナコ', 'introduction' => 'よろしくお願いします。'];
        $plainOffice = LawOffice::factory()->create(['name' => '大地法律事務所']);
        $byName = Lawyer::factory()->for($plainOffice)->create([...$base, 'name' => '青空 太郎']);
        $byKana = Lawyer::factory()->for($plainOffice)->create([...$base, 'name_kana' => 'アオゾラ ハナコ']);
        $byIntro = Lawyer::factory()->for($plainOffice)->create([...$base, 'introduction' => '青空のように明るく対応します。']);
        $byOffice = Lawyer::factory()
            ->for(LawOffice::factory()->create(['name' => '青空総合法律事務所']))
            ->create($base);
        Lawyer::factory()->for($plainOffice)->create($base);

        $response = $this->search(['keyword' => '青空'])->assertOk();

        $this->assertEqualsCanonicalizing([$byName->id, $byIntro->id, $byOffice->id], $this->ids($response));
        $this->assertSame([$byKana->id], $this->ids($this->search(['keyword' => 'アオゾラ'])->assertOk()));
    }

    public function test_search_空白区切りの2語_両方を含む弁護士だけを返す(): void
    {
        $office = $this->office();
        $both = Lawyer::factory()->for($office)->create(['name' => '山田 太郎', 'introduction' => '相続が得意です。']);
        Lawyer::factory()->for($office)->create(['name' => '山田 次郎', 'introduction' => '離婚が得意です。']);
        Lawyer::factory()->for($office)->create(['name' => '佐藤 花子', 'introduction' => '相続が得意です。']);

        $this->assertSame([$both->id], $this->ids($this->search(['keyword' => '山田 相続'])->assertOk()));
        $this->assertSame([$both->id], $this->ids($this->search(['keyword' => "山田\u{3000}相続"])->assertOk()));
    }

    public function test_search_3条件をすべて指定_すべてを満たす弁護士だけを返す(): void
    {
        $tokyo = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $osaka = Prefecture::factory()->create(['id' => 27, 'name' => '大阪府']);
        [$divorce, $traffic] = PracticeArea::factory()->count(2)->create();
        $tokyoOffice = LawOffice::factory()->for($tokyo)->create();
        $osakaOffice = LawOffice::factory()->for($osaka)->create();
        $intro = ['introduction' => '相続に強い事務所です。'];

        $match = Lawyer::factory()->for($tokyoOffice)->hasAttached($divorce)->create($intro);
        // 1条件ずつ外れる弁護士
        Lawyer::factory()->for($osakaOffice)->hasAttached($divorce)->create($intro);
        Lawyer::factory()->for($tokyoOffice)->hasAttached($traffic)->create($intro);
        Lawyer::factory()->for($tokyoOffice)->hasAttached($divorce)->create(['introduction' => '交通事故を扱います。']);

        $response = $this->search([
            'prefecture_id' => 13,
            'practice_area_ids' => [$divorce->id],
            'keyword' => '相続',
        ]);

        $this->assertSame([$match->id], $this->ids($response->assertOk()));
    }

    // ---- 正常系: ソート ----

    public function test_search_sort省略_実績数の多い順で同値はID昇順(): void
    {
        $office = $this->office();
        $a = Lawyer::factory()->for($office)->create(['case_count' => 100]);
        $b = Lawyer::factory()->for($office)->create(['case_count' => 300]);
        $c = Lawyer::factory()->for($office)->create(['case_count' => 100]);
        $d = Lawyer::factory()->for($office)->create(['case_count' => 200]);

        $this->assertSame([$b->id, $d->id, $a->id, $c->id], $this->ids($this->search()->assertOk()));
    }

    public function test_search_sortがcase_count_実績数の多い順で同値はID昇順(): void
    {
        $office = $this->office();
        $a = Lawyer::factory()->for($office)->create(['case_count' => 0]);
        $b = Lawyer::factory()->for($office)->create(['case_count' => 500]);
        $c = Lawyer::factory()->for($office)->create(['case_count' => 500]);

        $this->assertSame([$b->id, $c->id, $a->id], $this->ids($this->search(['sort' => 'case_count'])->assertOk()));
    }

    public function test_search_sortがage_生年月日の古い順で同値はID昇順(): void
    {
        $office = $this->office();
        $a = Lawyer::factory()->for($office)->bornOn('1980-05-02')->create();
        $b = Lawyer::factory()->for($office)->bornOn('1980-05-01')->create();
        $c = Lawyer::factory()->for($office)->bornOn('1960-01-01')->create();
        $d = Lawyer::factory()->for($office)->bornOn('1980-05-01')->create();

        $this->assertSame([$c->id, $b->id, $d->id, $a->id], $this->ids($this->search(['sort' => 'age'])->assertOk()));
    }

    public function test_search_sortがname_フリガナの五十音順で同値はID昇順(): void
    {
        $office = $this->office();
        $a = Lawyer::factory()->for($office)->create(['name_kana' => 'サトウ ハナコ']);
        $b = Lawyer::factory()->for($office)->create(['name_kana' => 'アベ タロウ']);
        $c = Lawyer::factory()->for($office)->create(['name_kana' => 'ワタナベ ジロウ']);
        $d = Lawyer::factory()->for($office)->create(['name_kana' => 'アベ タロウ']);
        $e = Lawyer::factory()->for($office)->create(['name_kana' => 'カトウ ケン']);

        $this->assertSame(
            [$b->id, $d->id, $e->id, $a->id, $c->id],
            $this->ids($this->search(['sort' => 'name'])->assertOk()),
        );
    }

    // ---- 正常系: ページネーション ----

    public function test_search_2ページ目を指定_21件目から返し1ページ目と重複しない(): void
    {
        Lawyer::factory()->count(25)->for($this->office())->create(['case_count' => 10]);

        $first = $this->ids($this->search()->assertOk());
        $second = $this->search(['page' => 2])
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.from', 21)
            ->assertJsonPath('meta.to', 25);

        $this->assertSame([], array_intersect($first, $this->ids($second)));
        // 全件が同値でも ID 昇順で欠落なくつながる
        $all = [...$first, ...$this->ids($second)];
        $sorted = $all;
        sort($sorted);
        $this->assertSame($sorted, $all);
        $this->assertCount(25, $all);
    }

    public function test_search_最終ページを超えるページ_200で空のdataを返す(): void
    {
        Lawyer::factory()->count(3)->for($this->office())->create();

        $this->search(['page' => 2])
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.from', null);
    }

    public function test_search_該当が0件_200で空のdataとtotal0を返す(): void
    {
        Lawyer::factory()->count(3)->for($this->office())->create(['name' => '山田 太郎', 'introduction' => 'よろしく']);

        $this->search(['keyword' => '該当しない語'])
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_search_条件とソートを指定_linksに検索条件を引き継ぐ(): void
    {
        Lawyer::factory()->count(25)->for($this->office())->create(['introduction' => '相続']);

        $next = $this->search(['keyword' => '相続', 'sort' => 'age'])->assertOk()->json('links.next');

        $this->assertStringContainsString('keyword='.urlencode('相続'), $next);
        $this->assertStringContainsString('sort=age', $next);
        $this->assertStringContainsString('page=2', $next);
    }

    public function test_search_パーセントを含むキーワード_文字として検索する(): void
    {
        $office = $this->office();
        $target = Lawyer::factory()->for($office)->create(['introduction' => '勝訴率100%を目指します。']);
        Lawyer::factory()->for($office)->create(['introduction' => '1000件以上の実績です。']);

        $this->assertSame([$target->id], $this->ids($this->search(['keyword' => '0%'])->assertOk()));
    }

    public function test_search_空のパラメータ_条件なしとして扱う(): void
    {
        Lawyer::factory()->count(3)->for($this->office())->create();

        $this->getJson('/api/v1/lawyers?prefecture_id=&keyword=&sort=&page=')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_search_keywordが100文字_200を返す(): void
    {
        $this->search(['keyword' => str_repeat('あ', 100)])->assertOk();
    }

    // ---- 異常系 ----

    public function test_search_存在しない都道府県ID_422を返す(): void
    {
        Prefecture::factory()->create(['id' => 47, 'name' => '沖縄県']);

        $this->search(['prefecture_id' => 48])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('prefecture_id');
    }

    public function test_search_数値でない都道府県ID_422を返す(): void
    {
        $this->search(['prefecture_id' => 'tokyo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('prefecture_id');
    }

    public function test_search_分野IDが配列でない_422を返す(): void
    {
        $area = PracticeArea::factory()->create();

        $this->search(['practice_area_ids' => $area->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('practice_area_ids');
    }

    public function test_search_存在しない分野ID_422を返す(): void
    {
        $area = PracticeArea::factory()->create();

        $this->search(['practice_area_ids' => [$area->id, 999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('practice_area_ids.1');
    }

    public function test_search_重複した分野ID_422を返す(): void
    {
        $area = PracticeArea::factory()->create();

        $this->search(['practice_area_ids' => [$area->id, $area->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['practice_area_ids.0', 'practice_area_ids.1']);
    }

    public function test_search_分野IDが13個_422を返す(): void
    {
        $this->search(['practice_area_ids' => range(1, 13)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('practice_area_ids');
    }

    public function test_search_keywordが101文字_422を返す(): void
    {
        $this->search(['keyword' => str_repeat('あ', 101)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('keyword');
    }

    public function test_search_keywordが配列_422を返す(): void
    {
        $this->search(['keyword' => ['山田']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('keyword');
    }

    public function test_search_定義外のsort_422を返す(): void
    {
        $this->search(['sort' => 'price'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_search_pageが0_422を返す(): void
    {
        $this->search(['page' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }

    public function test_search_pageが負数_422を返す(): void
    {
        $this->search(['page' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }

    public function test_search_pageが文字列_422を返す(): void
    {
        $this->search(['page' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }

    // ---- 性能 ----

    public function test_search_件数を増やす_SQL発行数が変わらない(): void
    {
        $areas = PracticeArea::factory()->count(3)->create();
        $officeA = $this->office();
        Lawyer::factory()->count(2)->for($officeA)->hasAttached($areas)->create(['introduction' => '相続']);
        $query = [
            'practice_area_ids' => $areas->modelKeys(),
            'keyword' => '相続',
            'sort' => 'age',
        ];

        $few = $this->countQueries(fn () => $this->search($query)->assertOk()->assertJsonCount(2, 'data'));

        // 別の事務所・都道府県も混ぜて20件にする
        Lawyer::factory()->count(9)->for($this->office())->hasAttached($areas)->create(['introduction' => '相続']);
        Lawyer::factory()->count(9)->for($this->office())->hasAttached($areas)->create(['introduction' => '相続']);
        $many = $this->countQueries(fn () => $this->search($query)->assertOk()->assertJsonCount(20, 'data'));

        $this->assertSame($few, $many);
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }
}
