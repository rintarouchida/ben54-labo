import { isRouteErrorResponse, Link, useRouteError } from 'react-router'
import { NotFoundPage } from './NotFoundPage'

/**
 * 画面の描画中に予期しない例外が起きたときの表示(React Router の errorElement)。
 * React Router 標準の開発者向け画面(英語・スタックトレース付き)の代わりに出す。
 */
export function ErrorPage() {
  const error = useRouteError()

  if (isRouteErrorResponse(error) && error.status === 404) {
    return <NotFoundPage />
  }

  // 原因の調査用に開発者ツールのコンソールにだけ残し、画面には出さない
  console.error(error)

  return (
    <section
      role="alert"
      className="mx-auto max-w-xl rounded-lg border border-red-200 bg-white px-6 py-12 text-center shadow-sm"
    >
      <title>エラーが発生しました | 弁護士検索</title>
      <h1 className="text-2xl font-bold text-red-800">エラーが発生しました</h1>
      <p className="mt-4 leading-relaxed text-gray-600">
        画面を表示できませんでした。再読み込みしても解決しない場合は、時間をおいて再度お試しください。
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <button
          type="button"
          onClick={() => window.location.reload()}
          className="rounded bg-blue-700 px-6 py-2.5 font-bold text-white hover:bg-blue-600"
        >
          再読み込み
        </button>
        <Link
          to="/"
          reloadDocument
          className="rounded border border-gray-300 bg-white px-6 py-2.5 hover:bg-gray-100"
        >
          弁護士検索のトップへ
        </Link>
      </div>
    </section>
  )
}
