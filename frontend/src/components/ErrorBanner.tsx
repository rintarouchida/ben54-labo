interface Props {
  /** 表示するメッセージ。空なら何も表示しない */
  messages: string[]
  /** 失敗した取得をやり直す。渡したときだけ「再読み込み」ボタンを出す */
  onRetry?: () => void
}

/**
 * 500・通信エラーを画面上部に出す共通の表示(F-18)。
 * スクロールしても見えるよう、ヘッダーの下に固定する。
 */
export function ErrorBanner({ messages, onRetry }: Props) {
  if (messages.length === 0) return null

  return (
    <div
      role="alert"
      className="sticky top-2 z-20 flex flex-wrap items-center justify-between gap-3 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800 shadow-md"
    >
      <ul className="space-y-1">
        {messages.map((message) => (
          <li key={message} className="flex gap-2">
            <span aria-hidden="true">⚠</span>
            {message}
          </li>
        ))}
      </ul>
      {onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="shrink-0 rounded border border-red-400 bg-white px-4 py-1.5 text-sm font-bold text-red-800 hover:bg-red-100"
        >
          再読み込み
        </button>
      )}
    </div>
  )
}
