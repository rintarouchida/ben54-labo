import { isAxiosError } from 'axios'
import type { ApiErrorBody, ValidationErrorBody } from '../types/api'

/** API が返した HTTP ステータス。通信エラー・タイムアウトなど応答がない場合は null */
export function getErrorStatus(error: unknown): number | null {
  return isAxiosError(error) ? (error.response?.status ?? null) : null
}

export function isNotFoundError(error: unknown): boolean {
  return getErrorStatus(error) === 404
}

/** 422 のときだけ項目ごとのエラーメッセージを返す */
export function getValidationErrors(
  error: unknown,
): Record<string, string[]> | null {
  if (!isAxiosError<ValidationErrorBody>(error)) return null
  if (error.response?.status !== 422) return null
  return error.response.data.errors ?? null
}

/** 画面上部に出すメッセージ。API の message があればそれを使う */
export function getErrorMessage(error: unknown): string {
  if (isAxiosError<ApiErrorBody>(error)) {
    if (!error.response) {
      return '通信エラーが発生しました。時間をおいて再度お試しください。'
    }
    // 429 の message は Laravel 標準の英語("Too Many Attempts.")のため、画面では日本語にする
    if (error.response.status === 429) {
      return 'アクセスが集中しています。1分ほど待ってから再度お試しください。'
    }
    const message = error.response.data?.message
    if (typeof message === 'string' && message !== '') return message
  }
  return 'エラーが発生しました。時間をおいて再度お試しください。'
}

/**
 * 422 の errors から、指定した項目(配列の要素 `name.0` なども含む)のメッセージを重複なく取り出す
 */
export function pickFieldErrors(
  errors: Record<string, string[]> | null,
  name: string,
): string[] {
  if (!errors) return []
  const messages = Object.entries(errors)
    .filter(([key]) => key === name || key.startsWith(`${name}.`))
    .flatMap(([, list]) => list)
  return [...new Set(messages)]
}
