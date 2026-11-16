import type { ReactNode } from 'react'
import { Link } from 'react-router'

type PageItem = number | 'ellipsis-start' | 'ellipsis-end'

/**
 * 表示するページ番号。先頭・末尾・現在のページの前後1ページを出し、間は「…」にする。
 * 「…」で隠れるのが1ページだけなら、その番号をそのまま出す。
 * 例: 現在5 / 全50 → 1 … 4 5 6 … 50
 */
function getPageItems(current: number, last: number): PageItem[] {
  const pages = new Set([1, last, current - 1, current, current + 1])
  const sorted = [...pages]
    .filter((p) => p >= 1 && p <= last)
    .sort((a, b) => a - b)

  const items: PageItem[] = []
  sorted.forEach((page, index) => {
    const prev = sorted[index - 1]
    if (prev !== undefined && page - prev === 2) items.push(prev + 1)
    if (prev !== undefined && page - prev > 2) {
      items.push(index === 1 ? 'ellipsis-start' : 'ellipsis-end')
    }
    items.push(page)
  })
  return items
}

interface Props {
  currentPage: number
  lastPage: number
  /** そのページを表示する URL のクエリ文字列(先頭の ? なし) */
  searchForPage: (page: number) => string
  /** ページを切り替えたとき(結果の先頭までスクロールする) */
  onNavigate: () => void
}

/** ページャー(F-12): 前へ / ページ番号 / 次へ */
export function Pagination({
  currentPage,
  lastPage,
  searchForPage,
  onNavigate,
}: Props) {
  if (lastPage <= 1) return null

  const pageLink = (page: number, children: ReactNode, label: string) => (
    <Link
      to={{ search: searchForPage(page) }}
      onClick={onNavigate}
      aria-label={label}
      className="flex h-10 min-w-9 items-center justify-center rounded border border-gray-300 bg-white px-2 text-sm hover:bg-blue-50 sm:min-w-10 sm:px-3"
    >
      {children}
    </Link>
  )
  const disabled = (children: ReactNode) => (
    <span
      aria-disabled="true"
      className="flex h-10 min-w-9 items-center justify-center rounded border border-gray-200 px-2 text-sm text-gray-400 sm:min-w-10 sm:px-3"
    >
      {children}
    </span>
  )

  return (
    <nav aria-label="ページ切り替え" className="flex justify-center">
      <ul className="flex flex-wrap items-center justify-center gap-1 sm:gap-1.5">
        <li>
          {currentPage > 1
            ? pageLink(currentPage - 1, '前へ', '前のページ')
            : disabled('前へ')}
        </li>
        {getPageItems(currentPage, lastPage).map((item) =>
          typeof item === 'number' ? (
            <li key={item}>
              {item === currentPage ? (
                <span
                  aria-current="page"
                  className="flex h-10 min-w-9 items-center justify-center rounded bg-blue-700 px-2 text-sm font-bold text-white sm:min-w-10 sm:px-3"
                >
                  {item}
                </span>
              ) : (
                pageLink(item, item, `${item}ページ目`)
              )}
            </li>
          ) : (
            <li
              key={item}
              aria-hidden="true"
              className="px-0.5 text-gray-500 sm:px-1"
            >
              …
            </li>
          ),
        )}
        <li>
          {currentPage < lastPage
            ? pageLink(currentPage + 1, '次へ', '次のページ')
            : disabled('次へ')}
        </li>
      </ul>
    </nav>
  )
}
