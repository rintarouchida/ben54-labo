import { Link, Outlet } from 'react-router'

/** 全画面共通のレイアウト(ヘッダー + 各画面) */
export function Layout() {
  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <header className="border-b border-gray-200 bg-white">
        <div className="mx-auto flex max-w-5xl items-center px-4 py-2">
          <Link
            to="/"
            className="py-1 text-xl font-bold text-blue-800 hover:text-blue-600"
          >
            弁護士検索
          </Link>
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  )
}
