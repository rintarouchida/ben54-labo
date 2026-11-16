import { Link } from 'react-router'
import type { LawyerSearchResponse, SortType } from '../types/api'
import { LawyerCard, LawyerCardSkeleton } from './LawyerCard'
import { Pagination } from './Pagination'
import { SortSelect } from './SortSelect'

/** 初回の読み込み中に並べるスケルトンの数 */
const SKELETON_COUNT = 6

interface Props {
  /** 未取得(初回の読み込み中・422)は undefined */
  result: LawyerSearchResponse | undefined
  /** 初回の読み込み中(表示できる結果がまだない) */
  isPending: boolean
  /** 条件を変えて読み込み中で、前の結果を表示している */
  isUpdating: boolean
  sort: string
  sortErrors: string[]
  onSortChange: (sort: SortType) => void
  /** 現在の検索条件(URL のクエリ文字列) */
  search: string
  searchForPage: (page: number) => string
  onPageNavigate: () => void
}

/** SCR-01 の下部: 結果ヘッダー・弁護士カード・ページャー(F-07〜F-13, F-17) */
export function SearchResults({
  result,
  isPending,
  isUpdating,
  sort,
  sortErrors,
  onSortChange,
  search,
  searchForPage,
  onPageNavigate,
}: Props) {
  return (
    <div className="space-y-4" aria-busy={isPending || isUpdating}>
      <div className="flex flex-wrap items-start justify-between gap-3">
        <ResultSummary
          result={result}
          isPending={isPending}
          isUpdating={isUpdating}
        />
        <SortSelect value={sort} errors={sortErrors} onChange={onSortChange} />
      </div>

      {isPending && (
        <ul className="grid gap-4 md:grid-cols-2">
          {Array.from({ length: SKELETON_COUNT }, (_, i) => (
            <li key={i}>
              <LawyerCardSkeleton />
            </li>
          ))}
        </ul>
      )}

      {result && result.meta.total === 0 && (
        <p className="rounded-lg border border-gray-200 bg-white p-8 text-center text-gray-700">
          条件に一致する弁護士が見つかりませんでした
        </p>
      )}

      {/* 最終ページを超えるページ(URL の書き換えなど)は API が空の data を返す */}
      {result && result.meta.total > 0 && result.data.length === 0 && (
        <p className="rounded-lg border border-gray-200 bg-white p-8 text-center text-gray-700">
          このページに表示する弁護士はいません。
          <Link
            to={{ search: searchForPage(1) }}
            className="ml-1 text-blue-700 underline hover:text-blue-500"
          >
            1ページ目へ
          </Link>
        </p>
      )}

      {result && result.data.length > 0 && (
        <>
          <ul
            className={`grid gap-4 transition-opacity md:grid-cols-2 ${isUpdating ? 'opacity-50' : ''}`}
          >
            {result.data.map((lawyer) => (
              <li key={lawyer.id}>
                <LawyerCard lawyer={lawyer} search={search} />
              </li>
            ))}
          </ul>
          <Pagination
            currentPage={result.meta.current_page}
            lastPage={result.meta.last_page}
            searchForPage={searchForPage}
            onNavigate={onPageNavigate}
          />
        </>
      )}
    </div>
  )
}

/** 「○○件中 1〜20件を表示」 */
function ResultSummary({
  result,
  isPending,
  isUpdating,
}: {
  result: LawyerSearchResponse | undefined
  isPending: boolean
  isUpdating: boolean
}) {
  if (!result) {
    return isPending ? <Spinner label="検索中…" /> : <div />
  }

  const { total, from, to } = result.meta
  return (
    <div className="flex items-center gap-3">
      <p aria-live="polite">
        <span className="text-lg font-bold">{total.toLocaleString()}</span>
        件中
        {from !== null && to !== null && (
          <>
            {' '}
            {from.toLocaleString()}〜{to.toLocaleString()}件を表示
          </>
        )}
      </p>
      {isUpdating && <Spinner label="更新中…" />}
    </div>
  )
}

function Spinner({ label }: { label: string }) {
  return (
    <span
      role="status"
      className="flex items-center gap-2 text-sm text-gray-500"
    >
      <span className="size-4 animate-spin rounded-full border-2 border-gray-300 border-t-blue-700" />
      {label}
    </span>
  )
}
