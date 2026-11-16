import axios from 'axios'

/**
 * API クライアント。`/api` へのリクエストは Vite のプロキシで api コンテナへ転送する。
 * 配列は `practice_area_ids[]=1&practice_area_ids[]=2` の形で送る(axios の既定の形式)。
 */
export const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: { Accept: 'application/json' },
  timeout: 10_000,
})
