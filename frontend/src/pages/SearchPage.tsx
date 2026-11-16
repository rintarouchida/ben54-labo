import { getErrorMessage } from '../api/errors'
import { usePracticeAreas, usePrefectures } from '../api/masters'

/**
 * SCR-01 弁護士検索・一覧
 * 今は API から取得したマスターを表示するだけ。検索フォームはチケット11、結果一覧はチケット12で作る。
 */
export function SearchPage() {
  const prefectures = usePrefectures()
  const practiceAreas = usePracticeAreas()

  return (
    <section>
      <h1 className="text-2xl font-bold">弁護士を探す</h1>

      <div className="mt-6 grid gap-6 sm:grid-cols-2">
        <div>
          <h2 className="font-bold">都道府県</h2>
          {prefectures.isPending && (
            <p className="mt-2 text-gray-500">読み込み中…</p>
          )}
          {prefectures.isError && (
            <p className="mt-2 text-red-700">
              {getErrorMessage(prefectures.error)}
            </p>
          )}
          {prefectures.isSuccess && (
            <p className="mt-2 text-sm">
              {prefectures.data.length}件:
              {prefectures.data.map((p) => p.name).join('、')}
            </p>
          )}
        </div>

        <div>
          <h2 className="font-bold">取扱分野</h2>
          {practiceAreas.isPending && (
            <p className="mt-2 text-gray-500">読み込み中…</p>
          )}
          {practiceAreas.isError && (
            <p className="mt-2 text-red-700">
              {getErrorMessage(practiceAreas.error)}
            </p>
          )}
          {practiceAreas.isSuccess && (
            <ul className="mt-2 flex flex-wrap gap-2">
              {practiceAreas.data.map((area) => (
                <li
                  key={area.id}
                  className="rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-800"
                >
                  {area.name}
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </section>
  )
}
