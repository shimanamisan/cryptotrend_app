<template>
  <div>
    <main class="l-main l-main__common">
      <h1 class="p-mypage__title">マイページ</h1>

      <div class="c-container__mypage u-margin__bottom--lg">
        <transition name="flash">
          <div class="u-flashmsg" v-show="flash_message_flg">
            <p>{{ this.systemMessage }}</p>
          </div>
        </transition>
        <div class="p-mypage__container">
          <div class="p-form__title">
            各種アカウント情報を変更できます。
          </div>
          <hr class="u-line" />
          <div class="p-mypage__content">
            <div class="p-mypage__content__body u-margin__bottom--m">
              <label for="nicname">ニックネーム</label>
              <input
                class="c-form__input"
                :class="{ 'c-error__input': errors_nicname }"
                type="text"
                name="nicname"
                v-model="userDataForm.nicname"
                placeholder="your username"
                @focus="clearError('nicname')"
              />
              <div v-if="errors_nicname" class="c-error">
                <ul v-if="errors_nicname">
                  <li v-for="msg in errors_nicname" :key="msg">
                    {{ msg }}
                  </li>
                </ul>
              </div>
            </div>

            <div class="p-mypage__content__body u-margin__bottom--m">
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

            <div class="p-mypage__content__body u-margin__bottom--m">
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
                  <li v-for="msg in errors_password" :key="msg">
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
            <div class="p-mypage__content__body u-margin__bottom--m">
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
export default {
  data() {
    return {
      userId: '',
      // 入力フォームデータバインディング
      userDataForm: {
        nicname: this.user,
        email: this.email,
        password: this.password,
        password_confirmation: this.password_confirmation,
      },
      errors_nicname: null,
      errors_email: null,
      errors_password: null,
      isset_pass: false,
      isDisabled: true,
      // 登録後のメッセージ表示フラグ
      flash_message_flg: false,
      systemMessage: '',
      // パスワード確認用モーダルの表示
      passwordModalWindow: false
    };
  },
  computed: {},
  methods: {
    isShowMessage() {
      this.flash_message_flg = !this.flash_message_flg;
    },
    isShowPasswordConfirmForm(){
      this.passwordModalWindow = !this.passwordModalWindow;
    },
    async getUserData() {
      const response = await axios
        .get('/mypage/user')
        .catch((error) => error.response || error);
      // console.log(response.data);
      if (response.status === 200) {
        this.userId = response.data.id;
        this.userDataForm.nicname = response.data.name;
        this.userDataForm.email = response.data.email;
        this.userDataForm.password = response.data.password;
        this.isset_pass = response.data.isset_pass;
      } else {
        alert('エラーが発生しました。しばらくお待ち下さい');
      }
    },
    async storUserData() {
      const response = await axios
        .post('/mypage/userdata', {
          id: this.userId,
          name: this.userDataForm.nicname,
          email: this.userDataForm.email,
          password: this.userDataForm.password,
          password_confirmation: this.userDataForm.password_confirmation,
        })
        .catch((error) => error.response || error);
      if (response.status === 200) {
        this.userId = response.data.user.id;
        this.userDataForm.nicname = response.data.user.name;
        this.userDataForm.email = response.data.user.email;
        this.userDataForm.password = null;
        this.userDataForm.password_confirmation = null;
        this.systemMessage = response.data.success;

        // フラッシュメッセージを表示
        this.isShowMessage();
        // 2秒後にメッセージを非表示にする
        setTimeout(this.isShowMessage, 2000);
      } else if (response.status === 422) {
        // console.log(response.data.errors);
        this.errors_nicname = response.data.errors.name;
        this.errors_email = response.data.errors.email;
        this.errors_password = response.data.errors.password;

        // // バリデーションで引っかかった場合は、パスワード入力フォームは空にする
        // this.userDataForm.password = null;
        // this.userDataForm.password_confirmation = null;
      } else {
        // 何か予期せぬErrorが発生したとき(500エラーなど)
        this.systemMessage = 'エラーが発生しました。しばらくお待ち下さい';
        this.isShowMessage();
        setTimeout(this.isShowMessage, 2000);
      }
    },
    async deleteUser() {
      if (confirm('退会します。よろしいですか？')) {
        const response = await axios
          .post('/mypage/delete')
          .catch((error) => error.response || error);
        // console.log(response);
        if (response.status === 200) {
          // 退会後ページを移動
          window.location = '/';
        } else {
          alert('エラーが発生しました。しばらくお待ち下さい');
          window.location = '/login';
        }
      }
    },
    cancelFrom() {
      this.errors = null;
      this.userDataForm.nicname = null;
      this.userDataForm.email = null;
      this.userDataForm.password = null;
      this.userDataForm.password_confirmation = null;
    },
    clearError(value) {
      if (value === 'nicname') {
        // console.log(typeof value);
        this.errors_nicname = null;
      } else if (value === 'email') {
        this.errors_email = null;
      } else if (value === 'pass') {
        this.errors_password = null;
      }
    },
  },
  created() {
    this.getUserData();
  },
};
</script>

<style></style>
