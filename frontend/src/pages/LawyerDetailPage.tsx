import type { ReactNode } from 'react'
import { Link, useLocation, useParams } from 'react-router'
import { getBannerMessages, isNotFoundError } from '../api/errors'
import { useLawyer } from '../api/lawyers'
import { ErrorBanner } from '../components/ErrorBanner'
import type { LawyerDetail } from '../types/api'
import { NotFoundPage } from './NotFoundPage'

/** API-02 のルートと同じく、先頭が1〜9の18桁までを有効な ID とする */
const VALID_ID = /^[1-9][0-9]{0,17}$/

/**
 * SCR-02 弁護士詳細(F-14〜F-17)
 * 数値でない ID と、API が404を返した ID は SCR-03 を表示する(F-16)。
 */
export function LawyerDetailPage() {
  const { id = '' } = useParams()

  if (!VALID_ID.test(id)) return <NotFoundPage />

  return <LawyerDetailContent id={Number(id)} />
}

/**
 * 「検索結果に戻る」のリンク先(F-15)。
 * 一覧のカードが state で渡した検索条件を使う。URL を直接開いたときは state がないため検索画面の初期表示に戻る。
 */
function useBackToResults(): string {
  const { state } = useLocation()
  const search: unknown = (state as { search?: unknown } | null)?.search
  return typeof search === 'string' && search !== '' ? `/?${search}` : '/'
}

function LawyerDetailContent({ id }: { id: number }) {
  const lawyer = useLawyer(id)
  const backTo = useBackToResults()

  if (lawyer.isError && isNotFoundError(lawyer.error)) return <NotFoundPage />

  return (
    <div className="space-y-6">
      <ErrorBanner
        messages={getBannerMessages([lawyer.error])}
        onRetry={() => void lawyer.refetch()}
      />
      <Link
        to={backTo}
        className="inline-flex items-center gap-1 text-blue-700 hover:text-blue-500 hover:underline"
      >
        <span aria-hidden="true">←</span>検索結果に戻る
      </Link>

      {lawyer.isPending && <DetailSkeleton />}
      {lawyer.isSuccess && <LawyerProfile lawyer={lawyer.data} />}
    </div>
  )
}

/** 1974-04-12 → 1974年4月12日 */
function formatDate(date: string): string {
  const [year, month, day] = date.split('-').map(Number)
  return `${year}年${month}月${day}日`
}

function LawyerProfile({ lawyer }: { lawyer: LawyerDetail }) {
  const office = lawyer.law_office

  return (
    <article className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
      <title>{`${lawyer.name} | 弁護士検索`}</title>

      <header className="border-b border-gray-200 pb-5">
        <p className="text-sm text-gray-500">{lawyer.name_kana}</p>
        <h1 className="text-2xl font-bold sm:text-3xl">{lawyer.name}</h1>
        {lawyer.practice_areas.length > 0 && (
          <ul className="mt-4 flex flex-wrap gap-2" aria-label="取扱分野">
            {lawyer.practice_areas.map((area) => (
              <li
                key={area.id}
                className="rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-800"
              >
                {area.name}
              </li>
            ))}
          </ul>
        )}
      </header>

      <dl className="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-[10rem_1fr]">
        <Item label="年齢">
          {lawyer.age}歳
          <span className="ml-2 text-sm text-gray-500">
            ({formatDate(lawyer.birth_date)}生まれ)
          </span>
        </Item>
        <Item label="弁護士登録年">{lawyer.registered_year}年</Item>
        <Item label="取扱裁判実績">
          <span className="text-lg font-bold">
            {lawyer.case_count.toLocaleString()}
          </span>
          件
        </Item>
        <Item label="取扱分野">
          {lawyer.practice_areas.length > 0
            ? lawyer.practice_areas.map((area) => area.name).join('、')
            : '登録なし'}
        </Item>
        <Item label="所属事務所">{office.name}</Item>
        <Item label="事務所住所">{office.address}</Item>
        <Item label="電話番号">
          <a
            href={`tel:${office.phone_number}`}
            className="text-blue-700 hover:text-blue-500 hover:underline"
          >
            {office.phone_number}
          </a>
        </Item>
      </dl>

      <section className="mt-8">
        <h2 className="border-l-4 border-blue-700 pl-3 text-lg font-bold">
          経歴
        </h2>
        <p className="mt-3 leading-relaxed whitespace-pre-line">
          {lawyer.career}
        </p>
      </section>

      <section className="mt-8">
        <h2 className="border-l-4 border-blue-700 pl-3 text-lg font-bold">
          自己紹介
        </h2>
        <p className="mt-3 leading-relaxed whitespace-pre-line">
          {lawyer.introduction}
        </p>
      </section>
    </article>
  )
}

function Item({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div className="contents">
      <dt className="text-sm font-bold text-gray-600 sm:pt-0.5">{label}</dt>
      <dd className="-mt-3 sm:mt-0">{children}</dd>
    </div>
  )
}

/** 読み込み中の表示(F-17) */
function DetailSkeleton() {
  return (
    <div
      role="status"
      aria-label="読み込み中"
      className="animate-pulse rounded-lg border border-gray-200 bg-white p-5 sm:p-8"
    >
      <div className="h-3 w-24 rounded bg-gray-100" />
      <div className="mt-2 h-8 w-48 rounded bg-gray-200" />
      <div className="mt-4 flex gap-2">
        <div className="h-6 w-20 rounded-full bg-gray-100" />
        <div className="h-6 w-20 rounded-full bg-gray-100" />
      </div>
      <div className="mt-8 space-y-4">
        {Array.from({ length: 7 }, (_, i) => (
          <div key={i} className="h-4 w-2/3 rounded bg-gray-100" />
        ))}
      </div>
      <div className="mt-8 h-24 rounded bg-gray-100" />
    </div>
  )
}
