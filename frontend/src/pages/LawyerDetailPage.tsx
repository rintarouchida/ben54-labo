import { useParams } from 'react-router'
import { getErrorMessage, isNotFoundError } from '../api/errors'
import { useLawyer } from '../api/lawyers'
import { NotFoundPage } from './NotFoundPage'

/** API-02 のルートと同じく、先頭が1〜9の18桁までを有効な ID とする */
const VALID_ID = /^[1-9][0-9]{0,17}$/

/**
 * SCR-02 弁護士詳細
 * 今は名前と事務所だけ表示する。全項目と「検索結果に戻る」はチケット13で作る。
 */
export function LawyerDetailPage() {
  const { id = '' } = useParams()

  if (!VALID_ID.test(id)) return <NotFoundPage />

  return <LawyerDetail id={Number(id)} />
}

function LawyerDetail({ id }: { id: number }) {
  const lawyer = useLawyer(id)

  if (lawyer.isPending) return <p className="text-gray-500">読み込み中…</p>
  if (lawyer.isError) {
    if (isNotFoundError(lawyer.error)) return <NotFoundPage />
    return <p className="text-red-700">{getErrorMessage(lawyer.error)}</p>
  }

  return (
    <section>
      <h1 className="text-2xl font-bold">{lawyer.data.name}</h1>
      <p className="mt-1 text-gray-600">{lawyer.data.name_kana}</p>
      <p className="mt-4">
        {lawyer.data.law_office.name}({lawyer.data.prefecture.name})
      </p>
    </section>
  )
}
