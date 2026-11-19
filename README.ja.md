# あむべ〜ぐる（AMU BAGEL）予約サイト

福岡県中間市のベーグル専門店「あむべ〜ぐる」の予約サイトです。
Webprogramming I の課題として、PHP + MySQL、HTML5 + vanilla CSS（フレームワークなし）で作ります。

## フォルダ構成

```text
amu-bagel/
├─ public/                 ← ブラウザから開くのはここだけ
│  ├─ admin/               管理画面のページ（ログイン必須）
│  ├─ assets/
│  │  ├─ css/              スタイルシート（vanilla CSS）
│  │  ├─ js/               補助的なJavaScript（テーマ切替など）
│  │  └─ img/              ロゴ・イラスト（プロトタイプから書き出したもの）
│  └─ uploads/products/    管理画面からアップロードした商品画像（Gitには入れない）
├─ includes/               全ページで使う共通のPHP（DB接続・ヘルパー・CSRF・認証など）
│  ├─ repositories/        SQLをテーブルごとにまとめた関数
│  └─ templates/           ヘッダー・フッターなどの部品
├─ lang/                   画面の文言（ja.php / en.php）
├─ config/                 DB接続などの設定（config.php はGitに入れない）
├─ database/               schema.sql（テーブル定義）と seed.sql（サンプルデータ）
└─ docs/                   提出用ドキュメントとスクリーンショット
```

お客さん向けページ（index.php, menu.php, cart.php など）は `public/` の直下に置きます。

## ローカル環境（XAMPP）

1. XAMPP（PHP 8.2）で Apache と MySQL を起動
2. このリポジトリを `htdocs/amu-bagel` に clone
3. phpMyAdmin で `amu_bagel` データベース（utf8mb4_unicode_ci）を作り、`database/schema.sql` → `seed.sql` をインポート
4. `config/config.sample.php` を `config/config.php` にコピーして接続情報を書く
5. `http://localhost/amu-bagel/public/` を開く

## イラスト

イラストはイラストレーターの @riin_and さんの作品です。
