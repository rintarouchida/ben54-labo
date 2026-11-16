import { QueryClient } from '@tanstack/react-query'
import { getErrorStatus } from './errors'

/** 4xx は結果が変わらないため再試行しない。通信エラーと 5xx は1回だけ再試行する */
function shouldRetry(failureCount: number, error: unknown): boolean {
  const status = getErrorStatus(error)
  if (status !== null && status >= 400 && status < 500) return false
  return failureCount < 1
}

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: shouldRetry,
      refetchOnWindowFocus: false,
      staleTime: 30_000,
    },
  },
})
