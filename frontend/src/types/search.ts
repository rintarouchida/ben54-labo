import type { SortType } from './api'

/** 検索フォームの入力値。セレクト・チェックボックスの値に合わせて文字列で持つ */
export interface SearchFormValues {
  prefectureId: string
  practiceAreaIds: string[]
  keyword: string
}

/** 4.1節のソートの選択肢。先頭が初期表示(F-08) */
export const SORT_OPTIONS: { value: SortType; label: string }[] = [
  { value: 'case_count', label: '実績数が多い順' },
  { value: 'age', label: '年齢が高い順' },
  { value: 'name', label: '名前順' },
]

export const DEFAULT_SORT: SortType = 'case_count'
