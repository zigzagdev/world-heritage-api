# World Heritage API / 世界遺産 API

Backend API for a world heritage study app targeting users preparing for the World Heritage Certification exam.
世界遺産検定を目指すユーザー向けの学習アプリのバックエンドAPIです。

Fetches and manages UNESCO World Heritage data, providing APIs for search, listing, and detail retrieval.
UNESCOの世界遺産データを取得・管理し、検索・一覧・詳細取得のAPIを提供します。

## Persona / ペルソナ

This API powers a study app for people preparing for the World Heritage Certification Exam.
このAPIは、世界遺産検定を受験する人向けの学習アプリを支えています。

Instead of carrying a heavy textbook, users can quickly look up heritage sites — their overview, location, and classification — from their smartphone or PC.
重いテキストを持ち歩かなくても、スマートフォンやPCから遺産の概要・場所・分類をすぐに調べられます。

The app targets anyone interested in the certification, regardless of age or experience.
年齢や経験を問わず、検定に興味があるすべての人を対象としています。

**Example User / ユーザー例**

Tanaka-san, 28, office worker.
Decided to take the World Heritage Certification Exam (Level 2).
During her commutation, she wants to quickly look up heritage sites on her phone,
but the official textbook is too heavy to carry around.
She needs a tool where she can check the location on a map along with the category and year of inscription — all in one place.

田中さん、28歳、会社員。
世界遺産検定2級の受験を決意。
通勤中にスマホでサッと調べたいけど、公式テキストは重くて持ち歩けない。
地図上の場所・カテゴリー・登録年を一度に確認できるツールを求めている。

## Tech Stack / 技術スタック

- PHP / Laravel
- MySQL (Aiven / クラウドデータベース管理)
- Algolia (Full-text search / 全文検索)
- Koyeb (Production hosting / 本番ホスティング)

## Challenge & Solution / 課題と解決策
### 検索の「ストレス」を排除し、「学び」を支える

Challenge / 課題 (Algoliaを選んだ理由):
Japanese search faces challenges with "notation fluctuations" (e.g., presence of middle dots or long vowels). Standard database queries often fail if even one character is different.
日本語には「中黒（・）」や「長音（ー）」の有無など、表記の揺れが多く、通常のDB検索では「一文字違うだけでヒットしない」という事案が発生する。

Solution(Why I choose Algolia) / 解決 (Algoliaを採用した理由):
So, by implementing Algolia, the system automatically handles these fluctuations. This ensures users find the information they need instantly without being penalized for minor input variations.
なので、全文検索エンジンを採用し、表記の揺れをシステム側でなくすようにしました。ユーザーはtypoを気にしすぎることなく、検索情報を得ることができる。

### Initial / Full rebuild / 初回・フル再構築
```bash
php artisan app:world-heritage-build --force --dump --jp --pretty
```

| Option | Description / 説明                                            |
|---|-------------------------------------------------------------|
| `--force` | Allow execution outside local/testing / ローカル・テスト環境以外での実行を許可 |
| `--dump` | Dump JSON from UNESCO / UNESCOからJSONをダンプ                    |
| `--jp` | Import Japanese names / 日本語名をインポート                          |
| `--pretty` | Pretty print JSON output / 見やすくするためのJSON出力を整形                          |

### Full rebuild including DB and Algolia / DBとAlgoliaを含むフル再構築
```bash
php artisan app:world-heritage-build \
  --fresh --jp --pretty \
  --translate-jp --translate-jp-from-json \
  --algolia --algolia-truncate \
  --force
```

`--fresh` wipes `world_heritage_descriptions`, so `--translate-jp --translate-jp-from-json` are required to repopulate Japanese descriptions from the cached translation JSON without calling the external translate API.
`--fresh` で `world_heritage_descriptions` が空になるため、外部翻訳 API を叩かずキャッシュ JSON から日本語訳を再投入する `--translate-jp --translate-jp-from-json` が必須です。

### Re-import Japanese names only / 日本語名のみ再インポート
```bash
php artisan world-heritage:import-japanese-names --force
```
## Architecture Overview / アーキテクチャ概要

Three arrow types, each one-way:
矢印は 3 種類で、それぞれ一方通行です:

- `▼` Vertical down — request flow through layers (top → bottom).
- `▼` 縦下向き — レイヤー間のリクエスト方向（上→下）。
- `▲` Vertical up — response flow back up the layers (bottom → top), transformed at each step.
- `▲` 縦上向き — レイヤー間のレスポンスの戻り（下→上）。各層で形を変えながら戻る。
- `◀` Horizontal — data lookup from Algolia into MySQL (search → DB).
- `◀` 横向き — Algolia から MySQL への ID 引き当て（検索→DB）。

```
                          [Browser]
                              │       ▲
                              ▼       │   HTTP request / JSON response
┌─────────────────────────────────────────────────────────────┐
│  Frontend (world-heritage-frontend)                         │
│  React + TypeScript + Vite / TailwindCSS                    │
└─────────────────────────────────────────────────────────────┘
                              │       ▲
                              ▼       │   REST call / JSON response
┌─────────────────────────────────────────────────────────────┐
│  Backend (world-heritage-api)                               │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Presentation Layer                                   │   │
│  │ Controller / ViewModel                               │   │
│  └─────────────┬────────────────────────────▲───────────┘   │
│                │                            │               │
│                ▼                            │               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Application Layer                                    │   │
│  │ UseCase / ListQuery / DTO                            │   │
│  └─────────────┬────────────────────────────▲───────────┘   │
│                │                            │               │
│                ▼                            │               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Domain Layer                                         │   │
│  │ Entity / QueryService Port                           │   │
│  └─────────────┬────────────────────────────▲───────────┘   │
│                │                            │               │
│                ▼                            │               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Infrastructure Layer                                 │   │
│  │ Eloquent Repository / Algolia Adapter                │   │
│  └────────┬─────────────────────────┬──────────────────┘    │
│           │                         │                       │
└───────────┼─────────────────────────┼───────────────────────┘
            ▼  list / detail          ▼  keyword search
       ┌─────────┐                ┌───────────┐
       │  MySQL  │  ◀──── ID ──── │  Algolia  │
       │  (DB)   │                │ (Search)  │
       └─────────┘                └───────────┘
```

Search path: Algolia returns matched IDs → DB lookup → Entity → DTO → ViewModel → JSON.
検索経路: Algolia がマッチした ID を返す → DB から本体取得 → Entity → DTO → ViewModel → JSON。

## Related Repositories / 関連リポジトリ

| Role / 役割 | Repository |
|---|---|
| Frontend / フロントエンド | https://github.com/zigzagdev/world-heritage-frontend |
| Backend API / バックエンドAPI | https://github.com/zigzagdev/world-heritage-api |

## Roadmap / 今後の予定
Planned next steps for the API (study-app-oriented):
学習アプリ向け API の今後の予定:

- Per-user favorites / bookmark of heritage sites.
  ユーザー作成を行い、それぞれのユーザーごとのお気に入り・ブックマーク機能を追記。