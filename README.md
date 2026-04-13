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

## Getting Started / はじめに
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

### Initial / Full rebuild / 初回・フル再構築
```bash
php artisan app:world-heritage-build --force --dump --jp --pretty
```

| Option | Description / 説明 |
|---|---|
| `--force` | Allow execution outside local/testing / ローカル・テスト環境以外での実行を許可 |
| `--dump` | Dump JSON from UNESCO / UNESCOからJSONをダンプ |
| `--jp` | Import Japanese names / 日本語名をインポート |
| `--pretty` | Pretty print JSON output / JSON出力を整形 |

### Full rebuild including DB and Algolia / DBとAlgoliaを含むフル再構築
```bash
php artisan app:world-heritage-build --fresh --jp --pretty --algolia --algolia-truncate --force
```

> :warning: `--fresh` drops and recreates all tables. All existing data will be lost.
> :warning: `--fresh` は全テーブルを削除して再作成します。

### Re-import Japanese names only / 日本語名のみ再インポート
```bash
php artisan world-heritage:import-japanese-names --force
```

## Testing / テスト
```bash
./vendor/bin/phpunit ./
```

## Architecture Overview / アーキテクチャ概要

```
[Browser]
    │
    ▼
┌─────────────────────────────────────────────────────────────┐
│  Frontend (world-heritage-frontend)                         │
│  React + TypeScript + Vite / TailwindCSS                    │
└─────────────────────────────────────────────────────────────┘
    │  REST API (HTTP)
    ▼
┌─────────────────────────────────────────────────────────────┐
│  Backend (world-heritage-api) / Laravel 11                  │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Presentation Layer                                   │   │
│  │ Controller / ViewModel                               │   │
│  └───────────────────┬──────────────────────────────────┘   │
│                      ▲ ViewModel                            │
│                      │                                      │
│  ┌───────────────────┴───────────────────────────────┐      │
│  │  Application Layer                                │      │
│  │  ListQuery / UseCase / DTO                        │      │
│  └───────────────────┬───────────────────────────────┘      │
│                      │                                      │
│                      ▼                                      │
│  ┌───────────────────────────────────────────────────┐      │
│  │  Domain Layer                                     │      │
│  │  Entity                                           │      │
│  └───────────────────┬───────────────────────────────┘      │
│                      │                                      │
│                      ▼                                      │
│  ┌───────────────────────────────────────────────────┐      │
│  │  Infrastructure Layer                             │      │
│  │  Eloquent Repository / QueryService               │      │
│  └───────────┬─────────────────────┬─────────────────┘      │
│              │                     │                        │
└──────────────┼─────────────────────┼────────────────────────┘
               │ 一覧 / 詳細取得       │ キーワード検索
               ▼                     ▼
          ┌─────────┐          ┌───────────┐
          │  MySQL  │◀─ ID ────│  Algolia  │
          │  (DB)   │          │ (Search)  │
          └─────────┘          └───────────┘

Flow: ListQuery → Domain(Entity) → DTO → ViewModel → Presentation
流れ： ListQuery → Domain(Entity) → DTO → ViewModel → Presentation
```

## Related Repositories / 関連リポジトリ

| Role / 役割 | Repository |
|---|---|
| Frontend / フロントエンド | https://github.com/zigzagdev/world-heritage-frontend |
| Backend API / バックエンドAPI | https://github.com/zigzagdev/world-heritage-api |