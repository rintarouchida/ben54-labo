import { createBrowserRouter } from 'react-router'
import { Layout } from './components/Layout'
import { ErrorPage } from './pages/ErrorPage'
import { LawyerDetailPage } from './pages/LawyerDetailPage'
import { NotFoundPage } from './pages/NotFoundPage'
import { SearchPage } from './pages/SearchPage'

/** 画面一覧(要件定義書 4章) */
export const router = createBrowserRouter([
  {
    element: <Layout />,
    // 共通レイアウト自体で例外が起きたときの受け皿
    errorElement: <ErrorPage />,
    children: [
      {
        // 各画面で予期しない例外が起きたら、ヘッダーを残したままエラー画面に切り替える
        errorElement: <ErrorPage />,
        children: [
          // SCR-01 弁護士検索・一覧
          { index: true, element: <SearchPage /> },
          // SCR-02 弁護士詳細
          { path: 'lawyers/:id', element: <LawyerDetailPage /> },
          // SCR-03 ページが見つかりません
          { path: '*', element: <NotFoundPage /> },
        ],
      },
    ],
  },
])
