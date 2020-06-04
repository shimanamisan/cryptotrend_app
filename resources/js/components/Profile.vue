<template>
  <div>
    <main class="l-main l-main__common">
      <div class="c-tabs__container c-tabs__container--md">
        <input type="radio" id="ranking" name="tab_item" checked />
        <label class="c-tabs__item c-tabs__item--full" for="ranking">プロフィール編集</label>
        <input type="radio" id="info" name="tab_item" />
        <label class="c-tabs__item c-tabs__item--full" for="info" name="tab_item">パスワード変更</label>
      </div>
      <div class="c-container__profile u-margin--bottom">
        <div class="p-profile__container">
          <div class="p-profile__body">
            <div class="p-profile__content">
              <p>プロフィール画像</p>
              <label class="p-profile__pic--ImgWrap" for>
                <input class="p-profile__pic--drop" type="file" />
                <output class>
                  <img class="p-profile__pic--output" src="https://placehold.jp/150x150.png" alt />
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
                <input class="c-form__input" type="text" name="nicname" />
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
                <input class="c-form__input" type="text" />
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
  data(){
    return{
      
    }
  },
  props: {
    endpoint: {
      type: String,
      default: ''
    }
  }
}
</script>

<style>
</style>