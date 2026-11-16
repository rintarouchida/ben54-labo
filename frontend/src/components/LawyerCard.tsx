import { Link } from 'react-router'
import type { LawyerSummary } from '../types/api'

interface Props {
  lawyer: LawyerSummary
  /** 詳細画面の「検索結果に戻る」で復元する検索条件(URL のクエリ文字列) */
  search: string
}

/** 検索結果の弁護士カード(F-07)。カード全体が詳細画面へのリンク */
export function LawyerCard({ lawyer, search }: Props) {
  return (
    <Link
      to={`/lawyers/${lawyer.id}`}
      state={{ search }}
      className="block h-full rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-400 hover:shadow-md focus-visible:outline-2 focus-visible:outline-blue-600"
    >
      <div className="flex items-baseline justify-between gap-2">
        <h2 className="text-lg font-bold text-blue-800">{lawyer.name}</h2>
        <span className="shrink-0 text-sm text-gray-600">{lawyer.age}歳</span>
      </div>
      <p className="text-xs text-gray-500">{lawyer.name_kana}</p>

      <p className="mt-3 text-sm">
        {lawyer.law_office.name}
        <span className="ml-2 text-gray-500">{lawyer.prefecture.name}</span>
      </p>

      {lawyer.practice_areas.length > 0 && (
        <ul className="mt-3 flex flex-wrap gap-1.5" aria-label="取扱分野">
          {lawyer.practice_areas.map((area) => (
            <li
              key={area.id}
              className="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs text-blue-800"
            >
              {area.name}
            </li>
          ))}
        </ul>
      )}

      <p className="mt-3 text-sm text-gray-700">
        取扱裁判実績
        <span className="ml-1 text-base font-bold text-gray-900">
          {lawyer.case_count.toLocaleString()}
        </span>
        件
      </p>
    </Link>
  )
}

/** 読み込み中に表示する、カードと同じ大きさの枠(F-17) */
export function LawyerCardSkeleton() {
  return (
    <div
      className="h-full animate-pulse rounded-lg border border-gray-200 bg-white p-4"
      aria-hidden="true"
    >
      <div className="h-5 w-1/3 rounded bg-gray-200" />
      <div className="mt-2 h-3 w-1/4 rounded bg-gray-100" />
      <div className="mt-4 h-4 w-2/3 rounded bg-gray-200" />
      <div className="mt-4 flex gap-2">
        <div className="h-5 w-16 rounded-full bg-gray-100" />
        <div className="h-5 w-16 rounded-full bg-gray-100" />
      </div>
      <div className="mt-4 h-4 w-1/3 rounded bg-gray-200" />
    </div>
  )
}
