import { Link } from 'react-router'

/** SCR-03 ページが見つかりません(未定義の URL、存在しない弁護士 ID。F-16) */
export function NotFoundPage() {
  return (
    <section className="mx-auto max-w-xl rounded-lg border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
      <title>ページが見つかりません | 弁護士検索</title>
      <p className="text-5xl font-bold text-blue-800" aria-hidden="true">
        404
      </p>
      <h1 className="mt-4 text-2xl font-bold">ページが見つかりません</h1>
      <p className="mt-4 leading-relaxed text-gray-600">
        お探しのページまたは弁護士の情報は、存在しないか削除された可能性があります。
        <br className="hidden sm:inline" />
        URL に誤りがないかご確認ください。
      </p>
      <Link
        to="/"
        className="mt-8 inline-block rounded bg-blue-700 px-6 py-2.5 font-bold text-white hover:bg-blue-600"
      >
        弁護士検索のトップへ
      </Link>
    </section>
  )
}
