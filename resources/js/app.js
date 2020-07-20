/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");
require("./jquery"); // jQueryで記述された処理を読み込む

// window.Vue = require("vue");

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component(
//     "example-component",
//     require("./components/ExampleComponent.vue").default
// );

// sass ファイル読み込み
import "../sass/style.scss";

import Vue from "vue";

import News from "./components/News.vue";
import UserList from "./components/UserList.vue";
import Mypage from "./components/Mypage.vue";
import Coins from "./components/Coins.vue";

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// console.logに#appが無いとエラーが出ないように変更
(function () {
    // #app idが付いた要素がない場合はVueインスタンスを生成しない
    if (!document.querySelector("#app")) {
        return null;
    } else {
        new Vue({
            el: "#app",
            components: {
                News,
                UserList,
                Coins,
                Mypage,
            },
        });
    }
})();
