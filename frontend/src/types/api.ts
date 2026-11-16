// API のレスポンス・リクエストの型(要件定義書 5章)

/** 都道府県(API-03) */
export interface Prefecture {
  id: number
  name: string
}

/** 取扱分野(API-04) */
export interface PracticeArea {
  id: number
  name: string
}

/** マスター一覧(API-03 / API-04)のレスポンス */
export interface ListResponse<T> {
  data: T[]
}

/** 検索結果の1件(API-01, 5.2節) */
export interface LawyerSummary {
  id: number
  name: string
  name_kana: string
  age: number
  case_count: number
  law_office: {
    id: number
    name: string
  }
  prefecture: Prefecture
  practice_areas: PracticeArea[]
}

/** 弁護士の詳細(API-02, 5.3節) */
export interface LawyerDetail {
  id: number
  name: string
  name_kana: string
  /** YYYY-MM-DD */
  birth_date: string
  age: number
  registered_year: number
  case_count: number
  career: string
  introduction: string
  law_office: {
    id: number
    name: string
    address: string
    phone_number: string
  }
  prefecture: Prefecture
  practice_areas: PracticeArea[]
}

/** API-02 のレスポンス */
export interface LawyerDetailResponse {
  data: LawyerDetail
}

/** Laravel のページネーションのリンク */
export interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

/** Laravel のページネーションの情報。0件のときは from / to が null */
export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}

/** API-01 のレスポンス */
export interface LawyerSearchResponse {
  data: LawyerSummary[]
  links: PaginationLinks
  meta: PaginationMeta
}

/** ソート種別(5.1節の `sort`) */
export type SortType = 'case_count' | 'age' | 'name'

/**
 * API-01 のリクエストパラメータ(5.1節)。省略したものは条件なし。
 * URL のクエリ文字列の値をそのまま渡し、検証は API に任せる(不正な値は422で返り、項目の近くに表示する)
 */
export interface LawyerSearchParams {
  prefecture_id?: string
  practice_area_ids?: string[]
  keyword?: string
  sort?: string
  page?: string
}

/** 404 / 429 / 500 のボディ(5.4節) */
export interface ApiErrorBody {
  message: string
}

/** 422 のボディ(5.4節)。キーはパラメータ名(配列の要素は `practice_area_ids.0` の形) */
export interface ValidationErrorBody extends ApiErrorBody {
  errors: Record<string, string[]>
}
