// productionモードでの圧縮方法、https://reffect.co.jp/html/webpack-4-mini-css-extract-plugin

const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const LiveReloadPlugin = require('webpack-livereload-plugin');
// app.jsとapp.cssファイルに分割するためのプラグイン
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const WebpackBuildNotifierPlugin = require('webpack-build-notifier'); 
// const BrowserSyncPlugin          = require('browser-sync-webpack-plugin');  


// [定数] webpack の出力オプションを指定します
// 'production' か 'development' を指定
const MODE = 'development';

console.log('ファイルパスを確認しています：' + `${__dirname}`);

// ソースマップの利用有無(productionのときはソースマップを利用しない)
const enabledSourceMap = MODE === 'development';

module.exports = {
  // モード値を production に設定すると最適化された状態で、
  // development に設定するとソースマップ有効でJSファイルが出力される
  mode: MODE,
  watch: true,
  // ${__dirname}が C:\Users\mikan\myVagrant\centos\project までのファイルパスになる
  // vagrantの共有フォルダからコードを書いているのでサーバ側のように/resourcesで始まるとディレクトリが見つからずエラーになる
  // babel-loader8 でasync/awaitを動作させるためには、@babel/polyfillが必要
  entry: ['@babel/polyfill', path.join(__dirname, 'resources/js/app.js')],
  // entry: `${__dirname}/resources/js/app.js`,
  output: {
    // 出力ファイル名
    filename: 'app.js',
    // 出力先フォルダを指定
    path: path.join(__dirname, `public/js`),
  },
  // 各種プラグインを読み込む
  plugins: [
    // Vueを読み込めるようにするため
    new VueLoaderPlugin(),
    new LiveReloadPlugin(),
    // jsファイルとcssファイルを分割するためのプラグイン
    new MiniCssExtractPlugin({
      // ファイルの出力先
      filename: '../css/style.css',
      // この記述ではpublic/js配下にstyle.cssが出力される
      // path: `${__dirname}/public/css`
    }),
    new WebpackBuildNotifierPlugin(),

    // new BrowserSyncPlugin({
    //   host: "host.cryptotrend",
    //   port: 80,
    //   proxy: {
    //     target: "host.cryptotrend",
    //   },
    //   files: [
    //       "resource/views/**/*.blade.php",
    //       // 公開フォルダを指定
    //       "public/**/*.*"
    //   ],
    //   open: "external"
    // })
  ],
  module: {
    rules: [
      {
        test: /\.css$/,
        // use: "vue-style-loader"
        use: ['vue-style-loader', 'css-loader'],
      },

      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        // Babel のオプションを指定する
        options: {
          presets: [
            // プリセットを指定することで、ES2020 を ES5 に変換
            '@babel/preset-env',
          ],
        },
      },
      // ESlintの設定
      // {
      //   test: /\.vue$/,
      //   exclude: /node_modules/,
      //   enforce: 'pre',
      //   use: [
      //       {
      //           loader: 'eslint-loader',
      //       },
      //   ]
      // },
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
            loader: 'css-loader',
            options: {
              // オプションでCSS内のurl()メソッドの取り込みを禁止 or 許可する
              url: false,
              // ソースマップを有効にする
              sourceMap: enabledSourceMap,
            },
          },
          // // linkタグに出力する機能
          // "style-loader",
          {
            loader: 'sass-loader',
            options: {
              // ソースマップの利用有無
              sourceMap: enabledSourceMap,
            },
          },
          {
            loader: 'import-glob-loader',
          },
          {
            // ベンダープレフィックス
            loader: 'postcss-loader',
            options: {
              plugins: [
                require('cssnano')({
                  // cssを圧縮
                  preset: 'default',
                }),
                // require("autoprefixer")({
                //     grid: true,
                //     browsers: [
                //         "last 1 version",
                //         "> 1%",
                //         "IE 10"
                //     ]
                // })
              ],
            },
          },
        ],
      },
      {
        // 対象となるファイルの拡張子
        test: /\.(gif|png|jpg|eot|wof|woff|woff2|ttf|svg)$/,
        // 画像をBase64として取り込む
        loader: 'url-loader',
      },
    ],
  },
  // import 文で .ts ファイルを解決するため
  resolve: {
    // Webpackで利用するときの設定
    alias: {
      vue$: 'vue/dist/vue.esm.js',
    },
    extensions: ['*', '.js', '.vue', '.json'],
  },
};
