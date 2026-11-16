import { createBrowserRouter } from 'react-router'
import { Layout } from './components/Layout'
import { LawyerDetailPage } from './pages/LawyerDetailPage'
import { NotFoundPage } from './pages/NotFoundPage'
import { SearchPage } from './pages/SearchPage'

/** 画面一覧(要件定義書 4章) */
export const router = createBrowserRouter([
  {
    element: <Layout />,
    children: [
      // SCR-01 弁護士検索・一覧
      { index: true, element: <SearchPage /> },
      // SCR-02 弁護士詳細
      { path: 'lawyers/:id', element: <LawyerDetailPage /> },
      // SCR-03 ページが見つかりません
      { path: '*', element: <NotFoundPage /> },
    ],
  },
])
