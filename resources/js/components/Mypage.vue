<template>
    <div>
        <main class="l-main l-main__common">
            <Loading v-show="loading" />
            <h1 class="c-title c-title__mypage">マイページ</h1>

            <div class="c-container__mypage u-margin__bottom--lg">
                <transition name="flash">
                    <div class="u-msg__flash" v-show="flash_message_flg">
                        <p>{{ this.systemMessage }}</p>
                    </div>
                </transition>
                <div class="p-mypage__container">
                    <div class="p-form__title">
                        各種アカウント情報を変更できます。
                    </div>
                    <hr class="u-line" />

                    <div class="p-mypage__content">
                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for="name"
                                >ニックネーム
                                <input
                                    id="name"
                                    class="c-form__input js-mypage-disabled-click"
                                    :class="{
                                        'c-error__input': errors_name,
                                    }"
                                    type="text"
                                    v-model="userDataForm.name"
                                    placeholder="your nicname"
                                    @focus="clearError('name')"
                                />
                            </label>
                            <span class="p-form__info--pass"
                                >※30文字以内で入力して下さい</span
                            >
                            <div v-if="errors_name" class="c-error">
                                <ul v-if="errors_name">
                                    <li v-for="msg in errors_name" :key="msg">
                                        {{ msg }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for="email">メールアドレス</label>

                            <input
                                id="email"
                                class="c-form__input js-mypage-disabled-click"
                                :class="{ 'c-error__input': errors_email }"
                                type="text"
                                v-model="userDataForm.email"
                                placeholder="email@example.com"
                                @focus="clearError('email')"
                            />

                            <div v-if="errors_email" class="c-error">
                                <ul v-if="errors_email">
                                    <li v-for="msg in errors_email" :key="msg">
                                        {{ msg }}
                                    </li>
                                </ul>
                            </div>

                            <div class="p-mypage__content--inwrap">
                                <div class="p-mypage__content--cancel">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--cancel"
                                        @click="cancelFromUserData"
                                    >
                                        入力をクリア
                                    </button>
                                </div>
                                <div class="p-mypage__content--submit">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--submit"
                                        @click="storUserData"
                                        :disabled="userDataSbumit_flg"
                                    >
                                        変更を保存
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-mypage__content">
                        <div class="p-form__title">
                            パスワードを変更する
                        </div>
                        <hr class="u-line" />
                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for="old_password">現在のパスワード</label>
                            <input
                                id="old_password"
                                class="c-form__input js-mypage-disabled-click"
                                :class="{
                                    'c-error__input': errors_old_password,
                                }"
                                type="password"
                                v-model="userPasswordDataForm.old_password"
                                placeholder="現在のパスワード"
                                @focus="clearError('old_pass')"
                            />
                            <div v-if="errors_old_password" class="c-error">
                                <ul v-if="errors_old_password">
                                    <li
                                        v-for="msg in errors_old_password"
                                        :key="msg"
                                    >
                                        {{ msg }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for="password">新しいパスワード</label>
                            <input
                                id="password"
                                class="c-form__input js-mypage-disabled-click"
                                :class="{ 'c-error__input': errors_password }"
                                type="password"
                                v-model="userPasswordDataForm.password"
                                placeholder="パスワード"
                                @focus="clearError('pass')"
                            />
                            <span class="p-form__info--pass"
                                >※半角英数8文字以上で入力して下さい</span
                            >
                            <div v-if="errors_password" class="c-error">
                                <ul v-if="errors_password">
                                    <li
                                        v-for="msg in errors_password"
                                        :key="msg"
                                    >
                                        {{ msg }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for="password_confirmation"
                                >新しいパスワードの確認</label
                            >
                            <input
                                id="password_confirmation"
                                class="c-form__input js-mypage-disabled-click"
                                type="password"
                                placeholder="パスワードの確認"
                                v-model="
                                    userPasswordDataForm.password_confirmation
                                "
                            />

                            <div class="p-mypage__content--inwrap">
                                <div class="p-mypage__content--cancel">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--cancel"
                                        @click="cancelFromPassword"
                                    >
                                        入力をクリア
                                    </button>
                                </div>
                                <div class="p-mypage__content--submit">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--submit"
                                        @click="changePasswordData"
                                        :disabled="passwordSbumit_flg"
                                    >
                                        変更を保存
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-form__title u-margin__top--xl">
                        SNS連携ステータス
                    </div>
                    <hr class="u-line" />

                    <div class="p-mypage__content__body u-margin__bottom--lg">
                        <template v-if="authTwuser">
                            <div
                                class="p-mypage__content__twauth u-margin__bottom--m"
                            >
                                <span class="u-margin__bottom--m"
                                    >Twitterアカウント連携中です</span
                                >
                                <button
                                    class="c-btn p-mypage__btn p-mypage__btn--submit"
                                    @click="clearTwitterAuth"
                                >
                                    Twitterアカウントの連携を解除する
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <div
                                class="p-mypage__content__twauth u-margin__bottom--m"
                            >
                                <span class="u-margin__bottom--m"
                                    >Twitterアカウントは未認証です</span
                                >
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="c-container__mypage">
                <div class="p-mypage__container p-mypage__dlcontent--common">
                    <h1 class="p-mypage__dltitle">アカウントの停止</h1>
                    <hr class="u-line" />
                    <div class="p-mypage__dlcontent">
                        <p>
                            退会処理を行います。アカウントの利用を停止すると、CryptoTrendにログインしたり、仮想通貨関連の情報が見れなくなります。
                        </p>
                    </div>
                    <button class="c-btn p-mypage__dlbtn" @click="deleteUser">
                        アカウントを停止する
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import Loading from "./module/Loading";
import { OK, UNPROCESSABLE_ENTITY, INTERNAL_SERVER_ERROR } from "./../util";

export default {
    components: {
        Loading,
    },
    data() {
        return {
            userId: "",
            authTwuser: false, // Twitterアカウントが認証済みのユーザーか判断する
            // 入力フォームデータバインディング
            userDataForm: {
                name: this.user,
                email: this.email,
            },
            userPasswordDataForm: {
                old_password: this.old_password,
                password: this.password,
                password_confirmation: this.password_confirmation,
            },
            errors_name: "", // バリデーションメッセージを格納する
            errors_email: "", // バリデーションメッセージを格納する
            errors_old_password: "", // バリデーションメッセージを格納する
            errors_password: "", // バリデーションメッセージを格納する
            systemMessage: "", // エラーメッセージ全般を格納する
            flash_message_flg: false, // 登録後のメッセージ表示フラグ
            loading: false, // 非同期通信時ローディングを表示する
            userDataSbumit_flg: true, // 送信ボタンを活性化・非活性化させるための判定用フラグ（ニックネーム、メールアドレス側）
            passwordSbumit_flg: true, // 送信ボタンを活性化・非活性化させるための判定用フラグ（パスワードフォーム側）
        };
    },
    computed: {},
    methods: {
        // 登録後のメッセージを表示させる
        isShowMessage() {
            this.flash_message_flg = !this.flash_message_flg;
        },
        // ユーザー情報を取得する
        async getUserData() {
            const response = await axios.get("/mypage/user");
            if (response.status === OK) {
                this.userId = response.data.id;
                this.userDataForm.name = response.data.name;
                this.userDataForm.email = response.data.email;
                if (response.data.my_twitter_id) {
                    this.authTwuser = true;
                }
            } else {
                alert("エラーが発生しました。しばらくお待ち下さい");
            }
        },
        // ニックネーム、メールアドレスのパスワードフォームを送信する
        async storUserData() {
            this.loadingActive();
            const response = await axios.post("/mypage/userdata", {
                id: this.userId,
                name: this.userDataForm.name,
                email: this.userDataForm.email,
            });
            console.log(response.data.success);
            if (response.status === OK) {
                this.loadingActive();

                // メールアドレスが変更された際は、ユーザー情報を返却していのでJavaScript側でエラーになって
                // フラッシュメッセージを出力させる処理まで実行されない。ユーザー情報を返却していなくとも
                // 処理としては通っているので、エラーで処理が停止しないようにtry catch構文で囲む
                try {
                    this.userId = response.data.user.id;
                    this.userDataForm.name = response.data.user.name;
                    this.userDataForm.email = response.data.user.email;
                    this.systemMessage = response.data.success;
                } catch (e) {
                    this.systemMessage = response.data.success;
                }

                // フラッシュメッセージを表示
                this.isShowMessage();
                // 2秒後にメッセージを非表示にする
                setTimeout(this.isShowMessage, 5000);
            } else if (response.status === UNPROCESSABLE_ENTITY) {
                this.loadingActive();
                this.errors_name = response.data.errors.name;
                this.errors_email = response.data.errors.email;
                this.errors_old_password = response.data.errors.old_password;
                this.errors_password = response.data.errors.password;
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                this.loadingActive();
                this.systemMessage =
                    "エラーが発生しました。しばらくお待ち下さい";
                this.isShowMessage();
                setTimeout(this.isShowMessage, 5000);
            }
        },
        // パスワードのフォームデータを送信する
        async changePasswordData() {
            this.loadingActive();
            const response = await axios.post("/mypage/change-password", {
                id: this.userId,
                old_password: this.userPasswordDataForm.old_password,
                password: this.userPasswordDataForm.password,
                password_confirmation: this.userPasswordDataForm
                    .password_confirmation,
            });
            if (response.status === OK) {
                this.loadingActive();
                this.userId = response.data.user.id;
                this.userPasswordDataForm.old_password = "";
                this.userPasswordDataForm.password = "";
                this.userPasswordDataForm.password_confirmation = "";
                this.systemMessage = response.data.success;

                // フラッシュメッセージを表示
                this.isShowMessage();
                // 2秒後にメッセージを非表示にする
                setTimeout(this.isShowMessage, 5000);
            } else if (response.status === UNPROCESSABLE_ENTITY) {
                this.loadingActive();
                this.errors_name = response.data.errors.name;
                this.errors_email = response.data.errors.email;
                this.errors_old_password = response.data.errors.old_password;
                this.errors_password = response.data.errors.password;
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                this.loadingActive();
                this.systemMessage =
                    "エラーが発生しました。しばらくお待ち下さい";
                this.isShowMessage();
                setTimeout(this.isShowMessage, 5000);
            }
        },
        // 退会処理を実行する
        async deleteUser() {
            if (confirm("CryptoTrendを退会します。よろしいですか？")) {
                const response = await axios.post("/mypage/delete");
                if (response.status === OK) {
                    // 退会後ページを移動
                    window.location = "/";
                } else {
                    alert("エラーが発生しました。しばらくお待ち下さい");
                    window.location = "/login";
                }
            }
        },
        // Twitterユーザーアカウントの認証を解除する
        async clearTwitterAuth() {
            if (
                confirm(
                    "Twitterアカウントの連携を解除します。\n よろしいですか？"
                )
            ) {
                this.loadingActive();
                const response = await axios.post("/mypage/clear-twuser");

                if (response.status === OK) {
                    this.loadingActive();
                    this.systemMessage = response.data.success;
                    this.authTwuser = false;
                    // フラッシュメッセージを表示
                    this.isShowMessage();
                    // 2秒後にメッセージを非表示にする
                    setTimeout(this.isShowMessage, 5000);
                } else {
                    // 何か予期せぬErrorが発生したとき(500エラーなど)
                    this.loadingActive();
                    this.systemMessage =
                        "エラーが発生しました。しばらくお待ち下さい";
                    this.isShowMessage();
                    setTimeout(this.isShowMessage, 5000);
                }
            }
        },
        // 入力フォームを全て空にする
        cancelFromUserData() {
            this.errors = "";
            this.userDataForm.name = "";
            this.userDataForm.email = "";
        },
        cancelFromPassword() {
            this.errors = "";
            this.userPasswordDataForm.old_password = "";
            this.userPasswordDataForm.password = "";
            this.userPasswordDataForm.password_confirmation = "";
        },
        // 入力フォームのエラーを空にする
        clearError(value) {
            if (value === "name") {
                this.errors_name = "";
            } else if (value === "email") {
                this.errors_email = "";
            } else if (value === "old_pass") {
                this.errors_old_password = "";
            } else if (value === "pass") {
                this.errors_password = "";
            }
        },
        // ローディング画面を表示させる
        loadingActive() {
            this.loading = !this.loading;
        },
    },
    watch: {
        // ニックネーム、メールアドレス側の監視
        userDataForm: {
            handler: function (val, oldval) {
                if (
                    val.name !== "" &&
                    val.name !== undefined &&
                    val.email !== "" &&
                    val.email !== undefined
                ) {
                    this.userDataSbumit_flg = false;
                } else {
                    this.userDataSbumit_flg = true;
                }
            },
            deep: true, // オブジェクトのネストされた値の更新を検出するためのオプション
        },
        // パスワードフォーム側の監視
        userPasswordDataForm: {
            handler: function (val, oldval) {
                if (
                    val.old_password !== "" &&
                    val.old_password !== undefined &&
                    val.password !== "" &&
                    val.password !== undefined &&
                    val.password_confirmation !== "" &&
                    val.password_confirmation !== undefined
                ) {
                    this.passwordSbumit_flg = false;
                } else {
                    this.passwordSbumit_flg = true;
                }
            },
            deep: true, // オブジェクトのネストされた値の更新を検出するためのオプション
        },
    },
    created() {
        this.getUserData();
    },
};
</script>
