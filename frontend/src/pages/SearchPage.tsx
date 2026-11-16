import { useMemo } from 'react'
import { useSearchParams } from 'react-router'
import {
  getErrorMessage,
  getValidationErrors,
  pickFieldErrors,
} from '../api/errors'
import { useLawyers } from '../api/lawyers'
import { SearchForm } from '../components/SearchForm'
import type { SearchFormValues } from '../types/search'
import { buildSearchQuery, parseSearchQuery, toFormValues } from './searchQuery'

/**
 * SCR-01 弁護士検索・一覧
 * 検索条件は URL のクエリ文字列だけに持ち、リロード・ブラウザバックでも同じ結果になる(F-05)。
 * 結果一覧・ソート・ページャーはチケット12で作る。
 */
export function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const params = useMemo(() => parseSearchQuery(searchParams), [searchParams])
  const lawyers = useLawyers(params)
  const validationErrors = getValidationErrors(lawyers.error)

  // フォームに入力欄がない項目(sort / page)の422は結果の上にまとめて出す
  const otherErrors = [
    ...pickFieldErrors(validationErrors, 'sort'),
    ...pickFieldErrors(validationErrors, 'page'),
  ]

  function handleSubmit(values: SearchFormValues) {
    setSearchParams(buildSearchQuery(values, params.sort))
  }

  function handleClear() {
    setSearchParams(new URLSearchParams())
  }

  return (
    <section className="space-y-6">
      <h1 className="text-2xl font-bold">弁護士を探す</h1>

      <SearchForm
        // URL が変わったら(検索・クリア・ブラウザバック)フォームを作り直し、URL の条件を入力欄に反映する
        key={searchParams.toString()}
        initialValues={toFormValues(params)}
        errors={validationErrors}
        onSubmit={handleSubmit}
        onClear={handleClear}
      />

      <div aria-live="polite">
        {otherErrors.length > 0 && (
          <ul className="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
            {otherErrors.map((message) => (
              <li key={message}>{message}</li>
            ))}
          </ul>
        )}
        {lawyers.isError && !validationErrors && (
          <p className="rounded border border-red-300 bg-red-50 p-3 text-red-700">
            {getErrorMessage(lawyers.error)}
          </p>
        )}
        {lawyers.isPending && <p className="text-gray-500">読み込み中…</p>}
        {lawyers.isSuccess && (
          <p>
            <span className="text-lg font-bold">
              {lawyers.data.meta.total.toLocaleString()}
            </span>
            件の弁護士が見つかりました
          </p>
        )}
      </div>
    </section>
  )
}
