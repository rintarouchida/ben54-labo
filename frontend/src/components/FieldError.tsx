interface Props {
  id: string
  messages: string[]
}

/** 入力項目の近くに出すエラー(F-18 の422) */
export function FieldError({ id, messages }: Props) {
  if (messages.length === 0) return null

  return (
    <ul id={id} className="mt-1 text-sm text-red-700" role="alert">
      {messages.map((message) => (
        <li key={message}>{message}</li>
      ))}
    </ul>
  )
}
