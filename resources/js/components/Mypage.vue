<template>
  <div>
    <main class="l-main l-main__common">
      <h1 class="p-mypage__title">マイページ</h1>

      <div class="c-container__mypage u-margin__bottom--lg">
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
                type="text"
                name="nicname"
                v-model="userDataForm.nicname"
              />
            </div>
            <div class="p-mypage__content__body u-margin__bottom--m">
              <label for>メールアドレス</label>
              <input
                class="c-form__input"
                type="text"
                v-model="userDataForm.email"
              />
            </div>

            <div class="p-mypage__content__body u-margin__bottom--m">
              <label for>新しいパスワード</label>
              <input
                class="c-form__input"
                type="password"
                v-model="userDataForm.password"
                placeholder="パスワード"
              />
              <template v-if="this.password !== null">
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
                v-model="userDataForm.password_confirm"
              />

              <div class="p-mypage__content--inwrap">
                <div class="p-mypage__content--cancel">
                  <button class="c-btn p-mypage__btn p-mypage__btn--cancel">
                    キャンセル
                  </button>
                </div>
                <div class="p-mypage__content--submit">
                  <button
                    class="c-btn p-mypage__btn p-mypage__btn--submit"
                    disabled="disabled"
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
          <button class="c-btn p-mypage__dlbtn">アカウントを停止する</button>
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
        password_confirm: this.password_confirm,
      },
      isDisabled: true,
    };
  },
  computed: {},
  methods: {
    async getUserData() {
      const response = await axios
        .get('/mypage/user')
        .catch((error) => error.response || error);
        console.log(response.data)
      if(response.status === 200){
        this.userId = response.data.id
        this.userDataForm.nicname = response.data.name
        this.userDataForm.email = response.data.email
        this.userDataForm.password = response.data.password
      }else{
        alert('エラーが発生しました。しばらくお待ち下さい')
      }
    },
  },
  created() {
    this.getUserData();
  },
};
</script>

<style></style>
