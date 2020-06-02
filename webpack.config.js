const { VueLoaderPlugin } = require("vue-loader");
const LiveReloadPlugin = require("webpack-livereload-plugin");
// app.jsとapp.cssファイルに分割するためのプラグイン
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

// [定数] webpack の出力オプションを指定します
// 'production' か 'development' を指定
const MODE = "development";

console.log(`${__dirname}/public/css`);

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = MODE === "development";

module.exports = {
    // モード値を production に設定すると最適化された状態で、
    // development に設定するとソースマップ有効でJSファイルが出力される
    mode: MODE,

    // ${__dirname}が C:\Users\mikan\myVagrant\centos\project までのファイルパスになる
    // vagrantの共有フォルダからコードを書いているのでサーバ側のように/resourcesで始まるとディレクトリが見つからずエラーになる
    entry: `${__dirname}/resources/js/app.js`,
    // entry: `${__dirname}/resources/js/app.js`,
    output: {
        // 出力ファイル名
        filename: "app.js",
        // 出力先フォルダを指定
        path: `${__dirname}/public/js`
    },
    // 各種プラグインを読み込む
    plugins: [
        // Vueを読み込めるようにするため
        new VueLoaderPlugin(),
        new LiveReloadPlugin(),
        new MiniCssExtractPlugin({
            filename: "../css/style.css"
            // この記述は必要ないのか
            // path: `${__dirname}/public/css`
        })
    ],
    module: {
        rules: [
            {
                test: /\.css$/,
                // use: "vue-style-loader"
                use: ["vue-style-loader", "css-loader"]
            },

            {
                test: /\.vue$/,
                loader: "vue-loader"
            },
            {
                test: /\.js$/,
                loader: "babel-loader",
                // Babel のオプションを指定する
                options: {
                    presets: [
                        // プリセットを指定することで、ES2020 を ES5 に変換
                        "@babel/preset-env"
                    ]
                }
            },
            {
                // 対象ファイルは style.scss
                test: /\.scss$/,
                // 対象ファイルは .css .scss .scss
                // test: /\.(sa|sc|c)ss$/,
                use: [
                    // CSSファイル生成
                    MiniCssExtractPlugin.loader,
                    // CSSコンパイル
                    {
                        loader: "css-loader",
                        options: {
                            // オプションでCSS内のurl()メソッドの取り込みを禁止 or 許可する
                            url: false,
                            // ソースマップを有効にする
                            sourceMap: enabledSourceMap
                        }
                    },
                    // // linkタグに出力する機能
                    // "style-loader",
                    {
                        loader: "sass-loader",
                        options: {
                            // ソースマップの利用有無
                            sourceMap: enabledSourceMap
                        }
                    },
                    {
                        loader: "import-glob-loader"
                    },
                    {
                        // ベンダープレフィックス
                        loader: "postcss-loader",
                        options: {
                            plugins: [
                                require("cssnano")({
                                    // cssを圧縮
                                    preset: "default"
                                })
                                // require("autoprefixer")({
                                //     grid: true,
                                //     browsers: [
                                //         "last 1 version",
                                //         "> 1%",
                                //         "IE 10"
                                //     ]
                                // })
                            ]
                        }
                    }
                ]
            },
            {
                // 対象となるファイルの拡張子
                test: /\.(gif|png|jpg|eot|wof|woff|woff2|ttf|svg)$/,
                // 画像をBase64として取り込む
                loader: "url-loader"
            }
        ]
    },
    // import 文で .ts ファイルを解決するため
    resolve: {
        // Webpackで利用するときの設定
        alias: {
            vue$: "vue/dist/vue.esm.js"
        },
        extensions: ["*", ".js", ".vue", ".json"]
    }
};
