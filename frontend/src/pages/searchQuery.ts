import type { LawyerSearchParams } from '../types/api'
import type { SearchFormValues } from '../types/search'

/**
 * SCR-01 の検索条件と URL のクエリ文字列の変換(F-05)。
 * 例: `/?prefecture_id=13&practice_area_ids[]=1&keyword=山田&sort=age&page=2`
 */
export const QUERY_KEYS = {
  prefectureId: 'prefecture_id',
  practiceAreaIds: 'practice_area_ids[]',
  keyword: 'keyword',
  sort: 'sort',
  page: 'page',
} as const

function nonEmpty(value: string | null): string | undefined {
  return value === null || value === '' ? undefined : value
}

/** URL のクエリ文字列から API-01 のパラメータを作る。値の検証は API に任せる */
export function parseSearchQuery(query: URLSearchParams): LawyerSearchParams {
  const practiceAreaIds = query
    .getAll(QUERY_KEYS.practiceAreaIds)
    .filter((id) => id !== '')

  return {
    prefecture_id: nonEmpty(query.get(QUERY_KEYS.prefectureId)),
    practice_area_ids: practiceAreaIds.length > 0 ? practiceAreaIds : undefined,
    keyword: nonEmpty(query.get(QUERY_KEYS.keyword)),
    sort: nonEmpty(query.get(QUERY_KEYS.sort)),
    page: nonEmpty(query.get(QUERY_KEYS.page)),
  }
}

export function toFormValues(params: LawyerSearchParams): SearchFormValues {
  return {
    prefectureId: params.prefecture_id ?? '',
    practiceAreaIds: params.practice_area_ids ?? [],
    keyword: params.keyword ?? '',
  }
}

/**
 * 検索ボタンで反映する URL のクエリ文字列を作る。
 * 未入力の項目は含めない。ソートは引き継ぎ、ページは含めない(1ページ目に戻す)
 */
export function buildSearchQuery(
  values: SearchFormValues,
  sort?: string,
): URLSearchParams {
  const query = new URLSearchParams()
  if (values.prefectureId !== '') {
    query.set(QUERY_KEYS.prefectureId, values.prefectureId)
  }
  // 同じ条件なら同じ URL になるよう、チェックした順ではなく ID 順に並べる
  const practiceAreaIds = [...values.practiceAreaIds].sort(
    (a, b) => Number(a) - Number(b),
  )
  for (const id of practiceAreaIds) {
    query.append(QUERY_KEYS.practiceAreaIds, id)
  }
  const keyword = values.keyword.trim()
  if (keyword !== '') query.set(QUERY_KEYS.keyword, keyword)
  if (sort) query.set(QUERY_KEYS.sort, sort)
  return query
}
