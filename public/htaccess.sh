#!/bin/sh
# ローカル用の記述から、本番環境用（httpsを有効にする）の記述に変更する処理です
cp .htaccess .htaccess_dev
cp .htaccess_deploy .htaccess