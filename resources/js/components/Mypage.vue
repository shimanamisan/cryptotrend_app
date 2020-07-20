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
                            <label for="name">ニックネーム</label>
                            <input
                                class="c-form__input"
                                :class="{ 'c-error__input': errors_name }"
                                type="text"
                                name="name"
                                v-model="userDataForm.name"
                                placeholder="your nicname"
                                @focus="clearError('name')"
                            />
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
                            <label for>メールアドレス</label>
                            <input
                                class="c-form__input"
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
                        </div>

                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for>新しいパスワード</label>
                            <input
                                class="c-form__input"
                                :class="{ 'c-error__input': errors_password }"
                                type="password"
                                v-model="userDataForm.password"
                                placeholder="パスワード"
                                @focus="clearError('pass')"
                            />
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
                            <template v-if="!this.isset_pass">
                                <span class="p-mypage__text"
                                    >※半角英数で8文字以上ご使用下さい</span
                                >
                                <br />
                                <span class="p-mypage__text"
                                    >※パスワードを追加するとメールアドレスでログイン出来ます。Twitterアカウントでもログイン出来ます。</span
                                >
                            </template>
                        </div>
                        <div
                            class="p-mypage__content__body u-margin__bottom--m"
                        >
                            <label for>新しいパスワードの確認</label>
                            <input
                                class="c-form__input"
                                type="password"
                                placeholder="パスワードの確認"
                                v-model="userDataForm.password_confirmation"
                            />

                            <div class="p-mypage__content--inwrap">
                                <div class="p-mypage__content--cancel">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--cancel"
                                        @click="cancelFrom"
                                    >
                                        入力をクリア
                                    </button>
                                </div>
                                <div class="p-mypage__content--submit">
                                    <button
                                        class="c-btn p-mypage__btn p-mypage__btn--submit"
                                        @click="storUserData"
                                        :disabled="sbumit_flg"
                                    >
                                        変更を保存
                                    </button>
                                </div>
                            </div>
                        </div>
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
            // 入力フォームデータバインディング
            userDataForm: {
                name: this.user,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation,
            },
            errors_name: "",
            errors_email: "",
            errors_password: "",
            isset_pass: false,
            // 登録後のメッセージ表示フラグ
            flash_message_flg: false,
            systemMessage: "",
            // パスワード確認用モーダルの表示
            passwordModalWindow: false,
            loading: false, // 非同期通信時ローディングを表示する
            sbumit_flg: true,
        };
    },
    computed: {},
    methods: {
        isShowMessage() {
            this.flash_message_flg = !this.flash_message_flg;
        },
        isShowPasswordConfirmForm() {
            this.passwordModalWindow = !this.passwordModalWindow;
        },
        async getUserData() {
            const response = await axios.get("/mypage/user");
            // .catch((error) => error.response || error);
            // console.log(response.data);
            if (response.status === OK) {
                this.userId = response.data.id;
                this.userDataForm.name = response.data.name;
                this.userDataForm.email = response.data.email;
                this.userDataForm.password = response.data.password;
                this.isset_pass = response.data.isset_pass;
            } else {
                alert("エラーが発生しました。しばらくお待ち下さい");
            }
        },
        async storUserData() {
            this.loadingActive();
            const response = await axios.post("/mypage/userdata", {
                id: this.userId,
                name: this.userDataForm.name,
                email: this.userDataForm.email,
                password: this.userDataForm.password,
                password_confirmation: this.userDataForm.password_confirmation,
            });
            // .catch((error) => error.response || error);
            if (response.status === OK) {
                this.loadingActive();
                this.userId = response.data.user.id;
                this.userDataForm.name = response.data.user.name;
                this.userDataForm.email = response.data.user.email;
                this.userDataForm.password = "";
                this.userDataForm.password_confirmation = "";
                this.systemMessage = response.data.success;

                // フラッシュメッセージを表示
                this.isShowMessage();
                // 2秒後にメッセージを非表示にする
                setTimeout(this.isShowMessage, 2000);
            } else if (response.status === UNPROCESSABLE_ENTITY) {
                this.loadingActive();
                this.errors_name = response.data.errors.name;
                this.errors_email = response.data.errors.email;
                this.errors_password = response.data.errors.password;
            } else {
                // 何か予期せぬErrorが発生したとき(500エラーなど)
                this.loadingActive();
                this.systemMessage =
                    "エラーが発生しました。しばらくお待ち下さい";
                this.isShowMessage();
                setTimeout(this.isShowMessage, 2000);
            }
        },
        async deleteUser() {
            if (confirm("CryptoTrendを退会します。よろしいですか？")) {
                const response = await axios
                    .post("/mypage/delete")
                    .catch((error) => error.response || error);
                // console.log(response);
                if (response.status === OK) {
                    // 退会後ページを移動
                    window.location = "/";
                } else {
                    alert("エラーが発生しました。しばらくお待ち下さい");
                    window.location = "/login";
                }
            }
        },
        cancelFrom() {
            this.errors = "";
            this.userDataForm.name = "";
            this.userDataForm.email = "";
            this.userDataForm.password = "";
            this.userDataForm.password_confirmation = "";
        },
        clearError(value) {
            if (value === "name") {
                // console.log(typeof value);
                this.errors_nicname = "";
            } else if (value === "email") {
                this.errors_email = "";
            } else if (value === "pass") {
                this.errors_password = "";
            }
        },
        loadingActive() {
            this.loading = !this.loading;
        },
    },
    watch: {
        userDataForm: {
            handler: function (val, oldval) {
                if (
                    val.name !== "" &&
                    val.name !== undefined &&
                    val.email !== "" &&
                    val.email !== undefined &&
                    val.password !== "" &&
                    val.password !== undefined &&
                    val.password_confirmation !== "" &&
                    val.password_confirmation !== undefined
                ) {
                    this.sbumit_flg = false;
                    console.log("全てのフォームが入力されています!");
                } else {
                    this.sbumit_flg = true;
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
