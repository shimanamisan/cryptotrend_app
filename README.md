# Twitter を利用した仮想通貨のトレンド分析サービス

## 機能概要

TwitterAPI を利用して、仮想通貨に関するツイートを銘柄毎に集計し、各銘柄の話題性を教えてくれるサービスです。
その他、仮想通貨関連アカウントの一覧を表示し、Google News から仮想通貨関連のニュースの一覧を表示します。

[アプリケーションはこちらからご覧になれます。]()

## 機能一覧

-   ユーザー登録
-   ログイン
-   Twitter アカウントの登録・ログイン
-   アカウントの削除
-   パスワードリマインダー
-   仮想通貨トレンド表示機能
-   仮想通貨関連ニュース表示機能
-   仮想通貨関連アカウント表示機能
    -   関連アカウントのフォロー機能
    -   関連アカウントの自動フォロー機能

## 使用技術

### 開発全般

-   Vagrant +CentOS8 の LAMP 環境、composer、npm、webpack、babel

### フロントエンド

-   Vue、sass、FLOCSS

### バックエンド

-   Laravel

## システム概要

### バッチ処理

以下の機能は Laravel のバッチ処理にて定期的にデータを取得し DB へ保存しています。

1. 仮想通貨関連アカウントの取得
2. 仮想通貨関連のツイートの取得
