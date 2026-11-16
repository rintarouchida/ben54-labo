import { Link } from 'react-router'

/** SCR-03 ページが見つかりません(未定義の URL、存在しない弁護士 ID) */
export function NotFoundPage() {
  return (
    <section className="py-12 text-center">
      <h1 className="text-2xl font-bold">ページが見つかりません</h1>
      <p className="mt-4 text-gray-600">
        お探しのページは存在しないか、削除された可能性があります。
      </p>
      <Link
        to="/"
        className="mt-6 inline-block text-blue-700 underline hover:text-blue-500"
      >
        弁護士検索に戻る
      </Link>
    </section>
  )
}
