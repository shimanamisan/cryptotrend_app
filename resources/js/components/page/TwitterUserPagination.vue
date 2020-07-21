<template>
    <div>
        <Loading v-show="loading" />
        <transition name="flash">
            <div class="u-msg__flash" v-show="flash_message_flg">
                <ul v-for="(msg, index) in this.systemMessage" :key="index">
                    <li>{{ msg }}</li>
                </ul>
            </div>
        </transition>
        <transition name="fade">
            <Modal
                v-show="open"
                @close-event="isModalActive"
                @autofollow-event="sendAutoFollowRequest"
                :autofollow_status="this.autoFollow_flg"
                ref="fromParent"
            />
        </transition>
        <section class="c-container c-container__twusr">
            <div class="p-twuser__header">
                <button
                    class="c-btn c-btn__common c-btn__common--autofollow"
                    :class="{ 'c-btn__disabled': !this.autoFollow_flg }"
                    @click="isModalActive"
                >
                    <p v-if="parseBoolean">現在自動フォロー機能ON</p>
                    <p v-else>現在自動フォロー機能OFF</p>
                </button>
                <p class="p-twuser__header__text">
                    {{ this.totalPage }}件中 {{ pageStart }} -
                    {{ endPage }}件まで表示
                </p>
                <p class="p-twuser__header__text">
                    {{ this.followcounter }}人フォロー済
                </p>
                <p>※1日に個別にフォロー/フォロー解除出来るのは20人が上限です</p>
            </div>
            <div
                class="p-twuser__card"
                v-for="(tw_userItem, index) in getTwitterUserItems"
                :key="index"
            >
                <template v-if="tw_userItem.isFollow">
                    <button
                        class="c-btn c-btn__common c-btn__common--unfollow u-btn__sm"
                        @click="sendUnFollowRequest(tw_userItem.id, index)"
                    >
                        フォロー解除する
                    </button>
                </template>
                <template v-else>
                    <button
                        class="c-btn c-btn__common c-btn__common--follow u-btn__sm"
                        @click="sendFollowRequest(tw_userItem.id, index)"
                    >
                        フォローする
                    </button>
                </template>
                <div class="p-twuser__detail__name">
                    {{ tw_userItem.user_name }}
                </div>
                <div class="p-twuser__detail__account">
                    ＠{{ tw_userItem.account_name }}
                </div>
                <div class="p-twuser__detail__description">
                    {{ tw_userItem.description }}
                </div>
                <div class="p-twuser__detail__item">
                    <p class="p-twuser__detail__item__count">
                        フォロー数:{{ tw_userItem.friends_count }}
                    </p>
                    <p class="p-twuser__detail__item__count">
                        フォロワー数:{{ tw_userItem.followers_count }}
                    </p>
                </div>
                <div class="p-twuser__detail">
                    <p class="p-twuser__detail__tweet__title">最新ツイート</p>
                    <div class="p-twuser__detail__tweet">
                        {{ tw_userItem.new_tweet }}
                    </div>
                </div>
            </div>
        </section>
        <paginate
            v-model="currentPage"
            :page-count="getPageCount"
            :page-range="3"
            :margin-pages="3"
            :click-handler="clickCallback"
            :first-last-button="true"
            :last-last-button="true"
            :disabled-class="'c-pagination__distable'"
            :first-button-text="'<<'"
            :first-button-class="'c-pagination__item--first'"
            :last-button-text="'>>'"
            :prev-text="'<'"
            :next-text="'>'"
            :containerClass="'c-pagination'"
            :page-class="'c-pagination__item'"
            :page-link-class="'c-pagination__link'"
            :prev-class="'c-pagination__item c-pagination__item--prev'"
            :prev-link-class="'c-pagination__link'"
            :next-class="'c-pagination__item c-pagination__item--next'"
            :next-link-class="'c-pagination__link'"
            :active-class="'c-pagination__item--active'"
            :hide-prev-next="true"
            :hide-first-last="true"
        ></paginate>
    </div>
</template>

<script>
import Vue from "vue";
import Paginate from "vuejs-paginate";
import Modal from "../module/Modal";
import Loading from "../module/Loading";
import { OK, UNPROCESSABLE_ENTITY, INTERNAL_SERVER_ERROR } from "./../../util"; // http通信のステータスコードの定数を読み込み
export default {
    props: ["follow_list", "total_page", "user"],
    components: {
        Paginate,
        Modal,
        Loading,
    },
    data() {
        return {
            totalPage: this.total_page,
            parPage: "",
            currentPage: 1,
            // 登録後のメッセージ表示フラグ
            flash_message_flg: false,
            systemMessage: "",
            autoFollow_flg: this.user.autofollow_status, // 自動フォロー中のフラグ(ユーザー情報より取得)
            followcounter: 0, // DBに保存されているユーザーを何人フォローしているかカウント
            open: false,
            loading: false, // 非同期通信時ローディングを表示する
        };
    },
    /********************************
     * メソッド
     ********************************/
    methods: {
        clickCallback(pageNum) {
            this.currentPage = pageNum;
            this.scrollTop();
        },
        paginationNumber() {
            // 表示するページネーションの数を割り出すために、総数を表示させる数で割っている
            this.parPage = Math.ceil(this.totalPage / 30);
        },
        // ページネーションをクリックした際にトップへ戻る
        scrollTop() {
            window.scrollTo({
                top: 0,
                behavior: "auto",
            });
        },
        isShowMessage() {
            this.flash_message_flg = !this.flash_message_flg;
        },
        isAlreadyUserMessage() {
            this.already_follow_user = !this.already_follow_user;
        },
        isModalActive() {
            this.open = !this.open;
            // モーダル開閉時に、背景をスクロール出来ないように固定する
            let $jsBg = document.getElementById("js-bg");
            $jsBg.classList.toggle("bg-gray__fix");
        },
        async sendAutoFollowRequest() {
            // catch(error => error.response || error)で非同期通信が成功しても失敗してもresponseに結果を代入する
            const response = await axios.post("/autofollow", {
                status: this.autoFollow_flg,
            });
            // .catch((error) => error.response || error);
            if (response.status === OK) {
                if (this.autoFollow_flg == 0) {
                    this.autoFollow_flg = 1;
                } else {
                    this.autoFollow_flg = 0;
                }

                this.sendingDone();
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                alert("問題が発生しました。しばらくお待ち下さい。");
            }
        },
        async sendFollowRequest(id, index) {
            this.loadingActive(); // ローディング画面を表示
            const response = await axios.post("/follow", { id: id });

            if (response.status === OK) {
                // 通信が成功した時の処理
                this.loadingActive();  // ローディング画面を非表示にする
                // 返却されたメッセージを格納
                this.systemMessage = response.data;
                // フラッシュメッセージを表示
                this.isShowMessage();
                // 2秒後にメッセージを非表示にする
                setTimeout(this.isShowMessage, 2000);
                // フォロー済みのステータスを通知する
                this.$emit("is-follow", id);
            } else if (response.status === 403) {
                this.loadingActive(); // ローディング画面を非表示にする
                // ユーザーを既にフォローしていた時の処理
                this.systemMessage = response.data;
                this.isShowMessage();
                setTimeout(this.isShowMessage, 2000);
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                alert("問題が発生しました。しばらくお待ち下さい。");
            }
        },
        async sendUnFollowRequest(id, index) {
            this.loadingActive(); // ローディング画面を表示
            const response = await axios.post("/unfollow", { id: id });
            // 通信が成功した時の処理
            if (response.status === OK) {
                this.loadingActive(); // ローディング画面を非表示にする
                // 返却されたメッセージを格納
                this.systemMessage = response.data;
                // フラッシュメッセージを表示
                this.isShowMessage();
                // 2秒後にメッセージを非表示にする
                setTimeout(this.isShowMessage, 2000);
                // フォロー済みのステータスを通知する
                this.$emit("is-unfollow", id);
            } else if (response.status === 403) {
                this.loadingActive(); // ローディング画面を非表示にする

                this.isShowMessage();
                this.systemMessage = response.data;
                setTimeout(this.isShowMessage, 2000);
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                alert("問題が発生しました。しばらくお待ち下さい。");
            }
        },
        followUserCounter() {
            let counter = this.follow_list.filter((item) => {
                return item.isFollow;
            });
            this.followcounter = counter.length;
        },
        sendingDone() {
            this.$refs.fromParent.sendingHandler();
        },
        loadingActive() {
            this.loading = !this.loading;
        },
    },
    /********************************
     * 算出プロパティ
     ********************************/
    computed: {
        // 表示させる要素を切り出す
        getTwitterUserItems() {
            let current = this.currentPage * this.parPage;
            let start = current - this.parPage;
            // sliceで配列を切り取る。index番号で指定するのでstartは0番から始まる
            return this.follow_list.slice(start, current);
        },
        getPageCount() {
            // 取得してきたユーザー情報の総数 ÷ 表示させるページネーション数
            return Math.ceil(this.follow_list.length / this.parPage);
        },
        pageStart() {
            let current = this.currentPage * this.parPage;
            let start = current - this.parPage;
            // 配列のインデックス番号で指定しているので、プラス1した値を返却する
            return ++start;
        },
        endPage() {
            let end = this.currentPage * this.parPage;
            return end;
        },
        // 自動フォロー中のフラグを元に判定用に真偽値にする（DBで指定したbooleanは0か1で入っているので）
        parseBoolean() {
            let flg;
            if (this.autoFollow_flg == 0) {
                let flg = false;
                return flg;
            } else {
                let flg = true;
                return flg;
            }
        },
    },
    /********************************
     * ウォッチャー
     ********************************/
    watch: {
        follow_list: {
            handler: function (newValue, oldValue) {
                this.followUserCounter();
            },
        },
    },
    created() {
        this.paginationNumber();
        this.followUserCounter();
    },
};
</script>
