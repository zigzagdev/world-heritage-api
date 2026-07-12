# CLAUDE.md

## Commit Rules

- コミットは必ず細分化する（1つの論理的変更 = 1コミット）
- コミットメッセージに Claude の署名（`Co-Authored-By`, `Generated with Claude Code` など）を含めない
- コミットメッセージは英語で記述する（例: `feat: add deleteUser to UserRepositroyInterface`）

## Branch Rules

- 機能ごとに親ブランチを切り、層ごとに子ブランチを切る
  - 例: `feat/user-delete`（親）
  - 例: `feat/user-delete_infra-layer`（子）
  - 例: `feat/user-delete_application-layer`（子）
  - 例: `feat/user-delete_presentation-layer`（子）
- 子ブランチは親ブランチへ PR を出す
- 親ブランチはさらに上位ブランチへ PR を出す

## PR Rules

### Title
- 日英併記にする
- 例: `feat: implement Presentation layer for DELETE /api/v1/users/{id} / ユーザー削除APIのPresentation層実装`

### Description
以下の構成で記述する:

```
## Motivation / 目的

（英語で動機・背景を説明。日本語でも補足する）

## What I have done / 実施内容

（実装内容を箇条書きで記述）

## Test Results / テスト結果

（実行したテストのメソッド名を列挙）

Closes #xxx
```

- PR description に Claude の署名（`Generated with Claude Code` など）を含めない

## Testing Rules

- テストファイルはすべて `app/Packages/` 配下に置く（`tests/` ではない）
  - phpunit.xml が `app/Packages` のみをスキャンするため
- Infra 層のテストは実 DB（Docker の `heritage-mysql-test`）を使ったインテグレーションテストにする（Mockery 不使用）
- Application 層・Domain 層のテストは Mockery を使ったユニットテストにする
- void を返すメソッドのテストでは `#[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]` アトリビュートを使う

## Architecture Rules (DDD / CQRS)

- `QueryUseCases`: 読み取り専用（副作用なし）
  - UseCase, Dto, Factory/Dto, ViewModel, Factory/ViewModel
- `CommandUseCases`: 書き込み（状態変更あり）
  - UseCase, UseCommand
- `UserRepositroyInterface` の引数は `UserEntity`（Command オブジェクトを直接渡さない）
- パスワードのハッシュ化は Application 層（UseCase）で行う
- パスワード変更は独立した別 API とし、更新系 Command には含めない

## Docker File Sync

- macOS + Docker Desktop ではファイル同期に遅延が発生することがある
- テストが古い挙動を示す場合は `docker cp` でファイルを強制同期する
  ```bash
  docker cp <host_path> heritage-app:<container_path>
  docker exec heritage-app php artisan route:clear
  ```
