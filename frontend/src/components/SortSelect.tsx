import type { SortType } from '../types/api'
import { SORT_OPTIONS } from '../types/search'
import { FieldError } from './FieldError'

interface Props {
  value: string
  errors: string[]
  onChange: (sort: SortType) => void
}

/** 結果ヘッダーの並び順の切り替え(F-08〜F-10) */
export function SortSelect({ value, errors, onChange }: Props) {
  const hasError = errors.length > 0

  return (
    <div>
      <div className="flex items-center gap-2">
        <label htmlFor="sort" className="text-sm font-bold whitespace-nowrap">
          並び順
        </label>
        <select
          id="sort"
          value={value}
          onChange={(event) => onChange(event.target.value as SortType)}
          aria-invalid={hasError}
          aria-describedby={hasError ? 'sort-error' : undefined}
          className="rounded border border-gray-300 bg-white px-3 py-1.5 text-sm aria-invalid:border-red-600"
        >
          {/* URL の sort が定義外のときも、選択中の値として残さず先頭を見せる */}
          {!SORT_OPTIONS.some((option) => option.value === value) && (
            <option value={value} disabled hidden>
              選択してください
            </option>
          )}
          {SORT_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>
      <FieldError id="sort-error" messages={errors} />
    </div>
  )
}
