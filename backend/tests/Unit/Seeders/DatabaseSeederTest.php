<?php

declare(strict_types=1);

namespace Tests\Unit\Seeders;

use App\Models\LawOffice;
use App\Models\Lawyer;
use App\Models\PracticeArea;
use App\Models\Prefecture;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_実行する_都道府県47件と分野12件と事務所300件と弁護士1000件が入る(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(47, Prefecture::count());
        $this->assertSame(12, PracticeArea::count());
        $this->assertSame(300, LawOffice::count());
        $this->assertSame(1000, Lawyer::count());
    }

    public function test_seeder_実行する_全員が1〜4分野を持つ(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = DB::table('lawyer_practice_area')
            ->selectRaw('lawyer_id, COUNT(*) AS c')
            ->groupBy('lawyer_id')
            ->pluck('c');

        $this->assertCount(1000, $counts);
        $this->assertGreaterThanOrEqual(1, $counts->min());
        $this->assertLessThanOrEqual(4, $counts->max());
    }

    public function test_seeder_実行する_年齢と登録年と実績数が定義の範囲に収まる(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lawyers = Lawyer::all();

        $this->assertGreaterThanOrEqual(25, $lawyers->min('age'));
        $this->assertLessThanOrEqual(80, $lawyers->max('age'));
        $this->assertGreaterThanOrEqual(0, $lawyers->min('case_count'));
        $this->assertLessThanOrEqual(500, $lawyers->max('case_count'));
        $this->assertTrue($lawyers->every(
            fn (Lawyer $l): bool => $l->registered_year >= $l->birth_date->year + 25 && $l->registered_year <= now()->year
        ));
    }

    public function test_seeder_実行する_事務所は人口の多い都道府県に偏る(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tokyo = LawOffice::where('prefecture_id', 13)->count();
        $tottori = LawOffice::where('prefecture_id', 31)->count();

        $this->assertGreaterThan($tottori, $tokyo);
    }

    public function test_seeder_2回実行する_同じデータになる(): void
    {
        $this->seed(DatabaseSeeder::class);
        $first = $this->snapshot();

        // DDL は暗黙コミットされるため、DELETE で消してから再投入する(ID は変わるので比較しない)
        DB::table('lawyer_practice_area')->delete();
        Lawyer::query()->delete();
        LawOffice::query()->delete();
        PracticeArea::query()->delete();
        Prefecture::query()->delete();
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($first, $this->snapshot());
    }

    /**
     * @return list<array<int, mixed>>
     */
    private function snapshot(): array
    {
        return Lawyer::with(['lawOffice', 'practiceAreas'])
            ->orderBy('id')
            ->get()
            ->map(fn (Lawyer $l): array => [
                $l->name,
                $l->name_kana,
                $l->birth_date->toDateString(),
                $l->registered_year,
                $l->case_count,
                $l->lawOffice->prefecture_id,
                $l->lawOffice->name,
                $l->lawOffice->address,
                $l->practiceAreas->pluck('name')->sort()->values()->all(),
            ])
            ->all();
    }
}
