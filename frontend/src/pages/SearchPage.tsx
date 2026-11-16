import { useMemo, useRef } from 'react'
import { useSearchParams } from 'react-router'
import {
  getBannerMessages,
  getValidationErrors,
  isBannerError,
  pickFieldErrors,
} from '../api/errors'
import { useLawyers } from '../api/lawyers'
import { usePracticeAreas, usePrefectures } from '../api/masters'
import { DummyDataNotice } from '../components/DummyDataNotice'
import { ErrorBanner } from '../components/ErrorBanner'
import { SearchForm } from '../components/SearchForm'
import { SearchResults } from '../components/SearchResults'
import type { SortType } from '../types/api'
import { DEFAULT_SORT, type SearchFormValues } from '../types/search'
import {
  buildSearchQuery,
  parseSearchQuery,
  toFormValues,
  withPage,
  withSort,
} from './searchQuery'

/**
 * SCR-01 弁護士検索・一覧
 * 検索条件・ソート・ページは URL のクエリ文字列だけに持ち、リロード・ブラウザバックでも同じ結果になる(F-05)。
 */
export function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const params = useMemo(() => parseSearchQuery(searchParams), [searchParams])
  const lawyers = useLawyers(params)
  const validationErrors = getValidationErrors(lawyers.error)
  // フォームの選択肢と同じキャッシュを使うため、追加のリクエストは発生しない
  const prefectures = usePrefectures()
  const practiceAreas = usePracticeAreas()

  // 500・通信エラーは画面上部にまとめて出し、失敗した取得だけをやり直せるようにする
  const failedQueries = [lawyers, prefectures, practiceAreas].filter((query) =>
    isBannerError(query.error),
  )
  const bannerMessages = getBannerMessages(failedQueries.map((q) => q.error))
  function retryFailed() {
    failedQueries.forEach((query) => void query.refetch())
  }
  const resultsRef = useRef<HTMLElement>(null)

  // 入力欄がない page の422は結果の上に出す
  const pageErrors = pickFieldErrors(validationErrors, 'page')

  function handleSubmit(values: SearchFormValues) {
    setSearchParams(buildSearchQuery(values, params.sort))
  }

  function handleClear() {
    setSearchParams(new URLSearchParams())
  }

  function handleSortChange(sort: SortType) {
    setSearchParams(withSort(searchParams, sort))
  }

  function scrollToResults() {
    resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return (
    <div className="space-y-8">
      <ErrorBanner messages={bannerMessages} onRetry={retryFailed} />
      <section className="space-y-4">
        <h1 className="text-2xl font-bold">弁護士を探す</h1>
        <DummyDataNotice />
        <SearchForm
          // URL が変わったら(検索・クリア・ブラウザバック)フォームを作り直し、URL の条件を入力欄に反映する
          key={searchParams.toString()}
          initialValues={toFormValues(params)}
          errors={validationErrors}
          onSubmit={handleSubmit}
          onClear={handleClear}
        />
      </section>

      <section
        ref={resultsRef}
        aria-label="検索結果"
        className="scroll-mt-4 space-y-4"
      >
        {pageErrors.length > 0 && (
          <ul
            role="alert"
            className="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700"
          >
            {pageErrors.map((message) => (
              <li key={message}>{message}</li>
            ))}
          </ul>
        )}
        {/* 422 でもソートの入力欄は出し、sort のエラーをその近くに表示する */}
        {(!lawyers.isError || validationErrors) && (
          <SearchResults
            result={lawyers.data}
            isPending={lawyers.isPending}
            isUpdating={lawyers.isPlaceholderData}
            sort={params.sort ?? DEFAULT_SORT}
            sortErrors={pickFieldErrors(validationErrors, 'sort')}
            onSortChange={handleSortChange}
            search={searchParams.toString()}
            searchForPage={(page) => withPage(searchParams, page).toString()}
            onPageNavigate={scrollToResults}
          />
        )}
      </section>
    </div>
  )
}
