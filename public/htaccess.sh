#!/bin/bash
# ローカル用の記述から、本番環境用（httpsを有効にする）の記述に変更する処理です

echo "###############################################"
echo "##                   START                   ##"
echo "###############################################"

# 作業ディレクトリを表示
echo ""
pwd
echo ""
# 既存の設定ファイルをコピー
cp .htaccess .htaccess_old
sleep 2
# コピー後に削除
rm .htaccess
sleep 2
# 本番環境用に用意したものをリネーム
cp .htaccess_production .htaccess
sleep 2
# コピー後に削除
rm .htaccess_production

echo ""
ls -la

echo ""
cat .htaccess

echo ""
echo "###############################################"
echo "##                    END                    ##"
echo "###############################################"