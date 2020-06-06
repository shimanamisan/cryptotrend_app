<template>
  <div>
    <main class="l-main l-main__common">
      <ul class="c-tabs__container c-tabs__container--md">
        <li
          class="c-tabs__item c-tabs__item--full"
          :class="{ 'c-tabs__item--active': tab === 1 }"
          @click="tab = 1"
        >プロフィール編集</li>
        <li
          class="c-tabs__item c-tabs__item--full"
          :class="{ 'c-tabs__item--active': tab === 2 }"
          @click="tab = 2"
        >パスワード変更</li>
      </ul>

      <div class="c-container__profile u-margin--bottom">
        <div class="p-profile__container">
          <div class="p-profile__body" v-show="tab === 1">
            <div class="p-profile__content">
              <p>プロフィール画像</p>
              <label class="p-profile__pic--ImgWrap" for>
                <input class="p-profile__pic--drop" type="file" />
                <output class>
                  <img class="p-profile__pic--output" :src="this.avatar" alt />
                </output>
              </label>
              <div class="p-profile__submitWrap">
                <div class="p-profile__content--cancel">
                  <button class="c-btn p-profile__btn p-profile__btn--cancel">キャンセル</button>
                </div>
                <div class="p-profile__content--submit">
                  <button class="c-btn p-profile__btn p-profile__btn--submit">変更</button>
                </div>
              </div>
            </div>

            <div class="p-profile__content">
              <div class="p-profile__content--body">
                <label for="nicname">ニックネーム</label>
                <input
                  class="c-form__input"
                  type="text"
                  name="nicname"
                  v-model="userDataForm.nicname"
                />
                <div class="p-profile__content--inwrap">
                  <div class="p-profile__content--cancel">
                    <button class="c-btn p-profile__btn p-profile__btn--cancel">キャンセル</button>
                  </div>
                  <div class="p-profile__content--submit">
                    <button class="c-btn p-profile__btn p-profile__btn--submit">変更</button>
                  </div>
                </div>
              </div>
              <div class="p-profile__content--body">
                <label for>メールアドレス</label>
                <input class="c-form__input" type="text" v-model="userDataForm.email" />
                <div class="p-profile__content--inwrap">
                  <div class="p-profile__content--cancel">
                    <button class="c-btn p-profile__btn p-profile__btn--cancel">キャンセル</button>
                  </div>
                  <div class="p-profile__content--submit">
                    <button class="c-btn p-profile__btn p-profile__btn--submit">変更</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="p-profile__body" v-show="tab === 2">
            <div class="p-profile__content--body">
              <label for>新しいパスワード</label>
              <input
                class="c-form__input u-margin__bottom--md"
                type="password"
                v-model="passwordEditForm.password"
              />

              <label for>新しいパスワードの確認</label>
              <input
                class="c-form__input u-margin__bottom--sd"
                type="password"
                v-model="passwordEditForm.password_confirm"
              />
              <div class="p-profile__content--inwrap">
                <div class="p-profile__content--submit u-btn__submit">
                  <button
                    class="c-btn p-profile__btn p-profile__btn--submit u-btn__submit"
                    :disabled="isDisabled"
                  >変更</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="p-profile__container">
          <div></div>
        </div>
      </div>
      <div class="c-container__profile">
        <div class="p-profile__dlbody">
          <h1 class="p-profile__dltitle">アカウントの削除</h1>
          <hr class="u-line" />
          <div class="p-profile__dlcontent">
            <p>退会処理を行います。登録している情報はすべて削除されます。</p>
          </div>
          <button class="c-btn p-profile__dlbtn">削除する</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
    data() {
        return {
            // ボタン表示フラグ
            editImage_flg: false,
            editName_flg: false,
            editEmail_flg: false,
            tab: 1,
            userDataForm: {
                nicname: this.user,
                email: this.email,
                avatar: this.avatar,
            },
            passwordEditForm: {
                password: "",
                password_confirm: "",
            },
            isDisabled: true,
        };
    },
    props: {
        endpoint: {
            type: String,
            default: "",
        },
        user: {
          type:String,
        },
        email: {
          type: String
        },
        avatar: {
          type: String
        }

    },
    computed: {},
};
</script>

<style></style>
