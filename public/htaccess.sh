#!/bin/sh
# ローカル用の記述から、本番環境用（httpsを有効にする）の記述に変更する処理です

# 既存の設定ファイルをコピー
# cp .htaccess .htaccess_old
# コピー後に削除
rm .htaccess
rm .htaccess_production
# 本番環境用に用意したものをリネーム
# cp .htaccess_production .htaccess