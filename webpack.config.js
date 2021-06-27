// productionモードでの圧縮方法、https://reffect.co.jp/html/webpack-4-mini-css-extract-plugin

const path = require("path");

const { VueLoaderPlugin } = require("vue-loader"); // vue-loaderの読み込み
const LiveReloadPlugin = require("webpack-livereload-plugin"); // LIVEリロードをするためのプラグイン
const MiniCssExtractPlugin = require("mini-css-extract-plugin"); // app.jsとapp.cssファイルに分割するためのプラグイン
const TerserPlugin = require("terser-webpack-plugin"); // JSのコメントをビルド時に削除する
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin"); // 別ファイルに出力したCSSファイルを圧縮するために必要
const WebpackBuildNotifierPlugin = require("webpack-build-notifier"); // 通知用プラグイン
const CopyPlugin = require('copy-webpack-plugin'); // ファイルをコピーするプラグイン
const ImageminPlugin = require('imagemin-webpack-plugin').default; // 各種画像形式の圧縮ツールを取りまとめる
const ImageminMozjpeg = require('imagemin-mozjpeg'); // jpgファイルを圧縮する
const ImageminMozpng = require('imagemin-pngquant'); // pngファイルを圧縮する

// [定数] webpack の出力オプションを指定します
// 'production' か 'development' を指定
const MODE = "development";
const mydir = path.resolve(__dirname);

console.log("ファイルパスを確認しています：" + mydir);

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = MODE === "production";

module.exports = {
    // モード値を production に設定すると最適化された状態で、
    // development に設定するとソースマップ有効でJSファイルが出力される
    mode: MODE,
    performance: { hints: false },
    // ${__dirname}が C:\Users\mikan\myVagrant\centos\project までのファイルパスになる
    // vagrantの共有フォルダからコードを書いているのでサーバ側のように/resourcesで始まるとディレクトリが見つからずエラーになる
    // babel-loader8 でasync/awaitを動作させるためには、@babel/polyfillが必要
    entry: ["@babel/polyfill", mydir + "/resources/js/app.js"],
    output: {
        // 出力ファイル名
        filename: "app.js",
        // 出力先フォルダを指定
        path: mydir + "/public/js",
    },
    // 最適化オプションを上書き
    optimization: {
        minimizer: [
            new TerserPlugin({
                extractComments: "all",
            }),
            new OptimizeCssAssetsPlugin({}),
        ],
    },
    module: {
        rules: [
            {
                // 対象ファイルは .css .scss .scss
                test: /\.(sa|sc|c)ss$/,
                use: [
                    // app.jsとapp.cssファイルに分割するためのプラグイン
                    MiniCssExtractPlugin.loader,

                    // CSSをバンドルするための機能
                    {
                        loader: "css-loader",
                        options: {
                            // url()を変換しない
                            url: false,
                            // ソースマップを有効にする
                            sourceMap: enabledSourceMap,
                        },
                    },
                    {
                        // Sassをバンドルするための機能
                        loader: "sass-loader",
                        options: {
                            // ソースマップの利用有無
                            sourceMap: enabledSourceMap,
                        },
                    },
                    {
                        loader: "import-glob-loader",
                    },
                    // PostCSSのための設定★
                    // バージョンが上がってからオプションの記述が変わっている
                    {
                        loader: "postcss-loader",
                        options: {
                            postcssOptions: {
                                plugins: [
                                  [
                                    "postcss-preset-env",
                                    {
                                        browsers: 'last 2 versions'
                                    },
                                  ],
                                ],
                            },
                        },
                    },
                ],
            },
            {
                // Vueファイルに対する設定
                test: /\.vue$/,
                use: [
                    {
                        loader: "vue-loader",
                        options: {
                            loaders: {
                                js: "babel-loader",
                            },
                            options: {
                                presets: [
                                    // プリセットを指定することで、ES2020 を ES5 に変換
                                    "@babel/preset-env",
                                ],
                            },
                        },
                    },
                ],
            },
            {
                // JSファイルに対する設定
                test: /\.js$/,
                use: [
                    {
                        loader: "babel-loader",
                        options: {
                            presets: [
                                // プリセットを指定することで、ES2020 を ES5 に変換
                                "@babel/preset-env",
                            ],
                        },
                    },
                ],
            },
            {
                // 対象となるファイルの拡張子
                test: /\.(gif|png|jpg|eot|wof|woff|woff2|ttf)$/,
                // 画像をBase64として取り込む
                loader: "url-loader",
            },
        ],
    },
    // 各種プラグインを読み込む
    plugins: [
        // Vueを読み込めるようにするため
        new VueLoaderPlugin(),
        // LIVEリロードするためのプラグイン
        new LiveReloadPlugin(),
        // jsファイルとcssファイルを分割するためのプラグイン
        new MiniCssExtractPlugin({
            // ファイルの出力先。エントリーポイントのjsディレクトリが基準となるので出力先には注意
            filename: "../css/style.css",
        }),
        new WebpackBuildNotifierPlugin(),
         // ファイルをコピーするプラグイン
         new CopyPlugin({
            patterns: [
              {
                from: path.resolve(__dirname, 'resources/images/'),
                to: path.resolve(__dirname, 'public/images/'),
                // from: "./resources/img/*",
                // to: "@/../../public/img", // ディレクトリ構成もコピーされる
                // context: ".src/sample",
              },
            ],
          }),
          new ImageminPlugin({
            test: /\.(jpe?g|jpg|png|gif|svg)$/i,
            pngquant: {
              quality: '50'
            },
            gifsicle: {
              interlaced: false,
              optimizationLevel: 1,
              colors: 256
            },
            svgo: {
            },
            plugins: [
              ImageminMozjpeg({
                quality: 60,
                progressive: true
              }),
              ImageminMozpng({})
            ]
          })
    ],
    // import 文で .ts ファイルを解決するため
    resolve: {
        // Webpackで利用するときの設定
        alias: {
            vue$: "vue/dist/vue.esm.js",
        },
        extensions: ["*", ".js", ".vue", ".json"],
    },
};
