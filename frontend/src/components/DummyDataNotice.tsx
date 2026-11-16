/** 架空のデータであることの注意書きに挙げる項目(Seeder が作るもの) */
const DUMMY_ITEMS = [
  '弁護士の氏名・フリガナ',
  '生年月日・年齢',
  '弁護士登録年',
  '取扱裁判実績数',
  '取扱分野',
  '経歴・自己紹介',
  '法律事務所名',
  '事務所の住所・電話番号',
]

/** 表示するデータがすべて架空であることの注意書き */
export function DummyDataNotice() {
  return (
    <aside
      aria-label="掲載データについての注意"
      className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm leading-relaxed text-red-700"
    >
      <p className="font-bold">
        ※
        このサイトに掲載している弁護士・法律事務所の情報は、すべて架空のものです。
      </p>
      <p className="mt-1">
        実在の人物・団体・住所・電話番号とは関係ありません。住所や電話番号への連絡はしないでください。
      </p>
      <p className="mt-1">架空のデータ:{DUMMY_ITEMS.join('、')}</p>
    </aside>
  )
}
