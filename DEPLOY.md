# Shiftboard - Renderへのデプロイ手順

## 🚀 デプロイ方法

### 1. GitHubリポジトリの準備

```bash
# まだGitリポジトリを作成していない場合
git init
git add .
git commit -m "Initial commit"

# GitHubにプッシュ
git remote add origin https://github.com/YOUR_USERNAME/shiftboard.git
git branch -M main
git push -u origin main
```

### 2. Renderアカウントの作成

1. https://render.com/ にアクセス
2. 「Get Started」をクリック
3. GitHubアカウントでサインアップ

### 3. Renderでデプロイ

#### オプションA: render.yaml を使用（おすすめ）

1. Renderダッシュボードで「New +」→「Blueprint」を選択
2. GitHubリポジトリを接続
3. リポジトリを選択
4. `render.yaml` が自動検出される
5. 「Apply」をクリック

#### オプションB: 手動で設定

##### データベースの作成:
1. Renderダッシュボードで「New +」→「PostgreSQL」または「MySQL」を選択
   - Name: `shiftboard-db`
   - Database: `shiftboard`
   - User: `app`
   - Region: Oregon (無料プランの場合)
   - Plan: Free

2. データベースが作成されたら、接続情報をメモ

##### Webサービスの作成:
1. Renderダッシュボードで「New +」→「Web Service」を選択
2. GitHubリポジトリを接続
3. 設定:
   - Name: `shiftboard-app`
   - Environment: `Docker`
   - Plan: `Free`
   - Branch: `main`

4. 環境変数を設定:
   - `FUEL_ENV`: `production`
   - `DB_HOST`: （データベースのホスト名）
   - `DB_PORT`: （データベースのポート番号）
   - `DB_DATABASE`: `shiftboard`
   - `DB_USERNAME`: （データベースのユーザー名）
   - `DB_PASSWORD`: （データベースのパスワード）

5. 「Create Web Service」をクリック

### 4. データベースのマイグレーション

デプロイ後、データベースのテーブルを作成する必要があります。

#### 方法1: Renderのシェルを使用

1. Renderダッシュボードで作成したWebサービスを開く
2. 「Shell」タブをクリック
3. 以下のコマンドを実行:

```bash
cd /var/www/html
php oil refine migrate
```

#### 方法2: ローカルから接続

1. データベースの接続情報を使用してローカルから接続
2. SQLファイルを直接インポート

### 5. 動作確認

1. Renderが自動生成したURL（例: `https://shiftboard-app.onrender.com`）にアクセス
2. アプリケーションが正常に動作することを確認

## 📝 注意事項

### 無料プランの制限

- **Webサービス**: 
  - 15分間アクセスがないとスリープ状態になる
  - 次のアクセス時に起動に数秒かかる
  - 月750時間まで無料

- **データベース**: 
  - 無料プランは90日後に削除される
  - 定期的にバックアップを取ることを推奨
  - 本番運用する場合は有料プラン（月$7〜）を検討

### 環境変数の確認

以下の環境変数が正しく設定されていることを確認:

```
FUEL_ENV=production
DB_HOST=（データベースのホスト）
DB_PORT=（データベースのポート）
DB_DATABASE=shiftboard
DB_USERNAME=（ユーザー名）
DB_PASSWORD=（パスワード）
```

### ログの確認

Renderダッシュボードの「Logs」タブでアプリケーションのログを確認できます。

エラーが発生した場合は、ここでエラーメッセージを確認してください。

## 🔧 トラブルシューティング

### デプロイが失敗する場合

1. Dockerfileが正しいか確認
2. 環境変数が正しく設定されているか確認
3. ログを確認してエラーメッセージを特定

### データベース接続エラー

1. 環境変数が正しいか確認
2. データベースサービスが起動しているか確認
3. ファイアウォールの設定を確認

### アプリケーションが起動しない

1. Apacheの設定を確認
2. .htaccessが正しく配置されているか確認
3. ファイルの権限を確認

## 📚 参考リンク

- [Render公式ドキュメント](https://render.com/docs)
- [Docker on Render](https://render.com/docs/docker)
- [FuelPHP公式ドキュメント](https://fuelphp.com/docs/)

## 💡 有料プランへのアップグレード

無料プランに満足できない場合、以下の有料プランを検討:

- **Starter**: 月$7 - スリープなし、より多いリソース
- **Standard**: 月$25 - さらに多いリソース、自動スケーリング

データベースも有料プランにアップグレード可能（月$7〜）

