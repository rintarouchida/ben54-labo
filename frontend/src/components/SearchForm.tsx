import { useState, type FormEvent } from 'react'
import { getErrorMessage, pickFieldErrors } from '../api/errors'
import { usePracticeAreas, usePrefectures } from '../api/masters'
import type { SearchFormValues } from '../types/search'
import { FieldError } from './FieldError'

/** 5.1節の keyword の上限 */
const KEYWORD_MAX_LENGTH = 100

interface Props {
  /** URL から復元した入力値。URL が変わったら key でフォームごと作り直して反映する */
  initialValues: SearchFormValues
  /** API-01 の422の errors */
  errors: Record<string, string[]> | null
  onSubmit: (values: SearchFormValues) => void
  onClear: () => void
}

/** SCR-01 の検索フォーム(F-01〜F-03, F-06, 4.1節) */
export function SearchForm({
  initialValues,
  errors,
  onSubmit,
  onClear,
}: Props) {
  const [values, setValues] = useState(initialValues)

  const prefectureErrors = pickFieldErrors(errors, 'prefecture_id')
  const practiceAreaErrors = pickFieldErrors(errors, 'practice_area_ids')
  const keywordErrors = pickFieldErrors(errors, 'keyword')

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    onSubmit(values)
  }

  function togglePracticeArea(id: string, checked: boolean) {
    setValues((current) => ({
      ...current,
      practiceAreaIds: checked
        ? [...current.practiceAreaIds, id]
        : current.practiceAreaIds.filter((value) => value !== id),
    }))
  }

  return (
    <form
      onSubmit={handleSubmit}
      className="space-y-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
      aria-label="弁護士の検索条件"
    >
      <div className="grid gap-5 sm:grid-cols-2">
        <PrefectureSelect
          value={values.prefectureId}
          errors={prefectureErrors}
          onChange={(prefectureId) =>
            setValues((current) => ({ ...current, prefectureId }))
          }
        />

        <div>
          <label htmlFor="keyword" className="block font-bold">
            キーワード
          </label>
          <input
            id="keyword"
            type="search"
            value={values.keyword}
            maxLength={KEYWORD_MAX_LENGTH}
            placeholder="名前・事務所名・自己紹介"
            onChange={(event) =>
              setValues((current) => ({
                ...current,
                keyword: event.target.value,
              }))
            }
            aria-invalid={keywordErrors.length > 0}
            aria-describedby={
              keywordErrors.length > 0 ? 'keyword-error' : undefined
            }
            className="mt-2 w-full rounded border border-gray-300 px-3 py-2 aria-invalid:border-red-600"
          />
          <FieldError id="keyword-error" messages={keywordErrors} />
        </div>
      </div>

      <PracticeAreaCheckboxes
        selected={values.practiceAreaIds}
        errors={practiceAreaErrors}
        onToggle={togglePracticeArea}
      />

      <div className="flex gap-3">
        <button
          type="submit"
          className="rounded bg-blue-700 px-6 py-2 font-bold text-white hover:bg-blue-600"
        >
          検索
        </button>
        <button
          type="button"
          onClick={onClear}
          className="rounded border border-gray-300 bg-white px-6 py-2 hover:bg-gray-100"
        >
          クリア
        </button>
      </div>
    </form>
  )
}

function PrefectureSelect({
  value,
  errors,
  onChange,
}: {
  value: string
  errors: string[]
  onChange: (value: string) => void
}) {
  const prefectures = usePrefectures()
  const hasError = errors.length > 0

  return (
    <div>
      <label htmlFor="prefecture" className="block font-bold">
        都道府県
      </label>
      <select
        id="prefecture"
        value={value}
        onChange={(event) => onChange(event.target.value)}
        disabled={!prefectures.isSuccess}
        aria-invalid={hasError}
        aria-describedby={hasError ? 'prefecture-error' : undefined}
        className="mt-2 w-full rounded border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100 aria-invalid:border-red-600"
      >
        <option value="">
          {prefectures.isPending ? '読み込み中…' : 'すべて'}
        </option>
        {prefectures.data?.map((prefecture) => (
          <option key={prefecture.id} value={String(prefecture.id)}>
            {prefecture.name}
          </option>
        ))}
      </select>
      {prefectures.isError && (
        <p className="mt-1 text-sm text-red-700">
          都道府県を取得できませんでした。{getErrorMessage(prefectures.error)}
        </p>
      )}
      <FieldError id="prefecture-error" messages={errors} />
    </div>
  )
}

function PracticeAreaCheckboxes({
  selected,
  errors,
  onToggle,
}: {
  selected: string[]
  errors: string[]
  onToggle: (id: string, checked: boolean) => void
}) {
  const practiceAreas = usePracticeAreas()
  const hasError = errors.length > 0

  return (
    <fieldset
      aria-invalid={hasError}
      aria-describedby={hasError ? 'practice-areas-error' : undefined}
    >
      <legend className="font-bold">取扱分野(複数選択可)</legend>
      {practiceAreas.isPending && (
        <p className="mt-2 text-sm text-gray-500">読み込み中…</p>
      )}
      {practiceAreas.isError && (
        <p className="mt-2 text-sm text-red-700">
          取扱分野を取得できませんでした。
          {getErrorMessage(practiceAreas.error)}
        </p>
      )}
      {practiceAreas.isSuccess && (
        <div className="mt-2 grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3 md:grid-cols-4">
          {practiceAreas.data.map((area) => {
            const id = String(area.id)
            return (
              <label key={area.id} className="flex items-center gap-2">
                <input
                  type="checkbox"
                  value={id}
                  checked={selected.includes(id)}
                  onChange={(event) => onToggle(id, event.target.checked)}
                  className="size-4"
                />
                {area.name}
              </label>
            )
          })}
        </div>
      )}
      <FieldError id="practice-areas-error" messages={errors} />
    </fieldset>
  )
}
