#!/bin/bash
# ローカル用の記述から、本番環境用（httpsを有効にする）の記述に変更する処理です

# 既存の設定ファイルをコピー
cp .htaccess .htaccess_old
# コピー後に削除
rm .htaccess
# 本番環境用に用意したものをリネーム
cp .htaccess_production .htaccess
# コピー後に削除
rm .htaccess_production

git status

pwd