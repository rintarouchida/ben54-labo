/** 検索フォームの入力値。セレクト・チェックボックスの値に合わせて文字列で持つ */
export interface SearchFormValues {
  prefectureId: string
  practiceAreaIds: string[]
  keyword: string
}
