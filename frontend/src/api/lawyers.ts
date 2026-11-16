import { keepPreviousData, useQuery } from '@tanstack/react-query'
import type {
  LawyerDetail,
  LawyerDetailResponse,
  LawyerSearchParams,
  LawyerSearchResponse,
} from '../types/api'
import { apiClient } from './client'

/** 空文字・空配列などの「指定なし」を送らないようにする */
function compactParams(params: LawyerSearchParams): LawyerSearchParams {
  const result: LawyerSearchParams = {}
  if (params.prefecture_id !== undefined)
    result.prefecture_id = params.prefecture_id
  if (params.practice_area_ids && params.practice_area_ids.length > 0) {
    result.practice_area_ids = params.practice_area_ids
  }
  const keyword = params.keyword?.trim()
  if (keyword) result.keyword = keyword
  if (params.sort !== undefined) result.sort = params.sort
  if (params.page !== undefined) result.page = params.page
  return result
}

/** API-01 */
export async function fetchLawyers(
  params: LawyerSearchParams,
): Promise<LawyerSearchResponse> {
  const { data } = await apiClient.get<LawyerSearchResponse>('/lawyers', {
    params: compactParams(params),
  })
  return data
}

/** API-02 */
export async function fetchLawyer(id: number): Promise<LawyerDetail> {
  const { data } = await apiClient.get<LawyerDetailResponse>(`/lawyers/${id}`)
  return data.data
}

export const lawyerKeys = {
  all: ['lawyers'] as const,
  search: (params: LawyerSearchParams) =>
    [...lawyerKeys.all, 'search', compactParams(params)] as const,
  detail: (id: number) => [...lawyerKeys.all, 'detail', id] as const,
}

/** 検索結果。条件を変えて読み込む間は前の結果を表示したままにする */
export function useLawyers(params: LawyerSearchParams) {
  return useQuery({
    queryKey: lawyerKeys.search(params),
    queryFn: () => fetchLawyers(params),
    placeholderData: keepPreviousData,
  })
}

/** 弁護士の詳細。404 は QueryClient の既定(4xx は再試行しない)により即座にエラーになる */
export function useLawyer(id: number) {
  return useQuery({
    queryKey: lawyerKeys.detail(id),
    queryFn: () => fetchLawyer(id),
  })
}
