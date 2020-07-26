// productionモードでの圧縮方法、https://reffect.co.jp/html/webpack-4-mini-css-extract-plugin

const path = require("path");
const { VueLoaderPlugin } = require("vue-loader");
// LIVEリロードをするためのプラグイン
const LiveReloadPlugin = require("webpack-livereload-plugin");
// app.jsとapp.cssファイルに分割するためのプラグイン
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
// JSのコメントをビルド時に削除する
const TerserPlugin = require("terser-webpack-plugin");
// 別ファイルに出力したCSSファイルを圧縮するために必要
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const WebpackBuildNotifierPlugin = require("webpack-build-notifier");

// [定数] webpack の出力オプションを指定します
// 'production' か 'development' を指定
const MODE = "development";

const mydir = path.resolve(__dirname);

console.log("ファイルパスを確認しています：" + mydir);

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = MODE === "development";

module.exports = {
    // モード値を production に設定すると最適化された状態で、
    // development に設定するとソースマップ有効でJSファイルが出力される
    mode: MODE,
    performance: { hints: false },
    // ${__dirname}が C:\Users\mikan\myVagrant\centos\project までのファイルパスになる
    // vagrantの共有フォルダからコードを書いているのでサーバ側のように/resourcesで始まるとディレクトリが見つからずエラーになる
    // babel-loader8 でasync/awaitを動作させるためには、@babel/polyfillが必要
    entry: ["@babel/polyfill", mydir + "/resources/js/app.js"],
    // entry: `${__dirname}/resources/js/app.js`,
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
                    {
                        loader: "postcss-loader",
                        options: {
                            // PostCSS側でもソースマップを有効にする
                            sourceMap: true,
                            plugins: [
                                // Autoprefixerを有効化
                                require("autoprefixer")({
                                    // ☆IEは11以上、Androidは4.4以上
                                    // その他は最新2バージョンで必要なベンダープレフィックスを付与する設定
                                    browsers: [
                                        "last 2 versions",
                                        "ie >= 11",
                                        "Android >= 4",
                                        "iOS >= 8",
                                    ],
                                }),
                            ],
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
                test: /\.(gif|png|jpg|eot|wof|woff|woff2|ttf|svg)$/,
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
