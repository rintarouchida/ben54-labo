import { useQuery } from '@tanstack/react-query'
import type { ListResponse, PracticeArea, Prefecture } from '../types/api'
import { apiClient } from './client'

/** API-03 */
export async function fetchPrefectures(): Promise<Prefecture[]> {
  const { data } = await apiClient.get<ListResponse<Prefecture>>('/prefectures')
  return data.data
}

/** API-04 */
export async function fetchPracticeAreas(): Promise<PracticeArea[]> {
  const { data } =
    await apiClient.get<ListResponse<PracticeArea>>('/practice-areas')
  return data.data
}

export const masterKeys = {
  prefectures: ['prefectures'] as const,
  practiceAreas: ['practice-areas'] as const,
}

// マスターは画面を開いている間に変わらないため、一度取得したら再取得しない
const MASTER_STALE_TIME = Infinity

export function usePrefectures() {
  return useQuery({
    queryKey: masterKeys.prefectures,
    queryFn: fetchPrefectures,
    staleTime: MASTER_STALE_TIME,
  })
}

export function usePracticeAreas() {
  return useQuery({
    queryKey: masterKeys.practiceAreas,
    queryFn: fetchPracticeAreas,
    staleTime: MASTER_STALE_TIME,
  })
}
