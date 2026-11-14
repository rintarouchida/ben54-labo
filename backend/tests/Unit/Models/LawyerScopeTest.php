<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use App\Models\Prefecture;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<int>
     */
    private function ids(Builder $query): array
    {
        return $query->orderBy('id')->pluck('id')->all();
    }

    private function createLawyer(array $attributes = [], ?LawOffice $office = null): Lawyer
    {
        $factory = Lawyer::factory();
        if ($office !== null) {
            $factory = $factory->for($office);
        }

        return $factory->create($attributes);
    }

    // ---- inPrefecture ----

    public function test_inPrefecture_都道府県IDを指定_その県の事務所に所属する弁護士だけを返す(): void
    {
        $tokyo = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $osaka = Prefecture::factory()->create(['id' => 27, 'name' => '大阪府']);
        $inTokyo = $this->createLawyer(office: LawOffice::factory()->for($tokyo)->create());
        $this->createLawyer(office: LawOffice::factory()->for($osaka)->create());

        $this->assertSame([$inTokyo->id], $this->ids(Lawyer::query()->inPrefecture(13)));
    }

    public function test_inPrefecture_null_絞り込まない(): void
    {
        Lawyer::factory()->count(3)->create();

        $this->assertCount(3, Lawyer::query()->inPrefecture(null)->get());
    }

    // ---- withAnyPracticeArea ----

    public function test_withAnyPracticeArea_分野を2つ指定_どちらかを扱う弁護士を重複なく返す(): void
    {
        [$divorce, $inheritance, $traffic] = PracticeArea::factory()->count(3)->create();
        $onlyDivorce = Lawyer::factory()->hasAttached($divorce)->create();
        $both = Lawyer::factory()->hasAttached([$divorce, $inheritance])->create();
        Lawyer::factory()->hasAttached($traffic)->create();

        $result = $this->ids(Lawyer::query()->withAnyPracticeArea([$divorce->id, $inheritance->id]));

        $this->assertSame([$onlyDivorce->id, $both->id], $result);
    }

    public function test_withAnyPracticeArea_null_絞り込まない(): void
    {
        Lawyer::factory()->count(2)->create();

        $this->assertCount(2, Lawyer::query()->withAnyPracticeArea(null)->get());
    }

    public function test_withAnyPracticeArea_空配列_絞り込まない(): void
    {
        Lawyer::factory()->count(2)->create();

        $this->assertCount(2, Lawyer::query()->withAnyPracticeArea([])->get());
    }

    // ---- matchKeyword ----

    public function test_matchKeyword_名前に含む_該当する弁護士を返す(): void
    {
        $target = $this->createLawyer(['name' => '山田 太郎', 'name_kana' => 'ヤマダ タロウ', 'introduction' => 'よろしく']);
        $this->createLawyer(['name' => '佐藤 花子', 'name_kana' => 'サトウ ハナコ', 'introduction' => 'よろしく']);

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('山田')));
    }

    public function test_matchKeyword_フリガナに含む_該当する弁護士を返す(): void
    {
        $target = $this->createLawyer(['name' => '山田 太郎', 'name_kana' => 'ヤマダ タロウ', 'introduction' => 'よろしく']);
        $this->createLawyer(['name' => '佐藤 花子', 'name_kana' => 'サトウ ハナコ', 'introduction' => 'よろしく']);

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('ヤマダ')));
    }

    public function test_matchKeyword_事務所名に含む_該当する弁護士を返す(): void
    {
        $target = $this->createLawyer(office: LawOffice::factory()->create(['name' => '青空総合法律事務所']));
        $this->createLawyer(office: LawOffice::factory()->create(['name' => '大地法律事務所']));

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('青空総合')));
    }

    public function test_matchKeyword_自己紹介に含む_該当する弁護士を返す(): void
    {
        $target = $this->createLawyer(['introduction' => '相続案件を得意としています。']);
        $this->createLawyer(['introduction' => '交通事故を中心に扱っています。']);

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('相続案件')));
    }

    public function test_matchKeyword_半角空白区切りの2語_両方を含む弁護士だけを返す(): void
    {
        $both = $this->createLawyer(['name' => '山田 太郎', 'introduction' => '相続が得意です。']);
        $this->createLawyer(['name' => '山田 次郎', 'introduction' => '離婚が得意です。']);
        $this->createLawyer(['name' => '佐藤 花子', 'introduction' => '相続が得意です。']);

        $this->assertSame([$both->id], $this->ids(Lawyer::query()->matchKeyword('山田 相続')));
    }

    public function test_matchKeyword_全角空白区切りの2語_両方を含む弁護士だけを返す(): void
    {
        $both = $this->createLawyer(['name' => '山田 太郎', 'introduction' => '相続が得意です。']);
        $this->createLawyer(['name' => '山田 次郎', 'introduction' => '離婚が得意です。']);

        $this->assertSame([$both->id], $this->ids(Lawyer::query()->matchKeyword("山田\u{3000}相続")));
    }

    public function test_matchKeyword_パーセントを含む_ワイルドカードとして扱わない(): void
    {
        $target = $this->createLawyer(['introduction' => '勝訴率100%を目指します。']);
        $this->createLawyer(['introduction' => '勝訴率1000件以上の実績です。']);

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('100%')));
    }

    public function test_matchKeyword_アンダースコアを含む_1文字のワイルドカードとして扱わない(): void
    {
        $target = $this->createLawyer(['introduction' => 'ID_A です。']);
        $this->createLawyer(['introduction' => 'IDXA です。']);

        $this->assertSame([$target->id], $this->ids(Lawyer::query()->matchKeyword('ID_A')));
    }

    public function test_matchKeyword_null_絞り込まない(): void
    {
        Lawyer::factory()->count(2)->create();

        $this->assertCount(2, Lawyer::query()->matchKeyword(null)->get());
    }

    public function test_matchKeyword_空文字と空白のみ_絞り込まない(): void
    {
        Lawyer::factory()->count(2)->create();

        $this->assertCount(2, Lawyer::query()->matchKeyword('')->get());
        $this->assertCount(2, Lawyer::query()->matchKeyword(" \u{3000} ")->get());
    }

    // ---- リレーション ----

    public function test_リレーション_弁護士から事務所と都道府県と分野をたどれる(): void
    {
        $prefecture = Prefecture::factory()->create(['id' => 13, 'name' => '東京都']);
        $office = LawOffice::factory()->for($prefecture)->create();
        $area = PracticeArea::factory()->create();
        $lawyer = Lawyer::factory()->for($office)->hasAttached($area)->create();

        $lawyer->load('lawOffice.prefecture', 'practiceAreas');

        $this->assertSame('東京都', $lawyer->lawOffice->prefecture->name);
        $this->assertSame([$area->id], $lawyer->practiceAreas->pluck('id')->all());
        $this->assertSame([$lawyer->id], $prefecture->lawOffices->first()->lawyers->pluck('id')->all());
        $this->assertSame([$lawyer->id], $area->lawyers->pluck('id')->all());
    }
}
