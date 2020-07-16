<template>
  <div class="p-modal">
    <div class="p-modal__container">
      <div class="p-modal__body">
        <div class="p-modal__nav">
          <i class="fas fa-times p-modal__nav--close" @click="closeTrigger"></i>
        </div>
        <div class="p-modal__body__section">
          <ul>
            <li class="p-modal__body__list">
              自動フォロー機能をONにすると、一定間隔で関連アカウントを自動でフォローします。
            </li>
            <li class="p-modal__body__list">
              全てのユーザーをフォローすると、この機能はOFFになります。
            </li>
            <li class="p-modal__body__list">
              個別フォローを行う際は、自動フォローの処理と重複する可能性があります。
            </li>
            <li class="p-modal__body__list">
              自動フォロー機能はいつでも解除できます。
            </li>
          </ul>

          <template v-if="!followStatus">
            <div class="p-modal__wrap p-modal__wrap__btn">
              <button class="c-btn c-btn__common" @click="sendAutoFollowTrigger">
                <div class="p-modal__wrap__btn--in" v-if="sending">
                  <div class="p-modal__send p-modal__send--motion"></div>
                  <p class="p-modal__text">通信中...</p>
                </div>
                <p v-else>自動フォロー機能をONにする</p>
              </button>
            </div>
          </template>
          <template v-else>
            <div class="p-modal__wrap p-modal__wrap__btn">
              <button class="c-btn c-btn__common" @click="sendAutoFollowTrigger">
                <div class="p-modal__wrap__btn--in" v-if="sending">
                  <div class="p-modal__send p-modal__send--motion"></div>
                  <p class="p-modal__text">通信中...</p>
                </div>
                <p v-else>自動フォロー機能をOFFにする</p>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      sending: false,
    };
  },
  props: ['autofollow_status'],
  methods: {
    closeTrigger() {
      this.$emit('close-event');
    },
    sendAutoFollowTrigger(){
      this.$emit('autofollow-event')
      this.sending = !this.sending
    },
    // axios通信完了時に親コンポーネントからこのメソッドを呼び出す
    sendingHandler(){
      this.sending = !this.sending
      this.$emit('close-event');
    }
  },
  computed: {
    followStatus() {
      if (this.autofollow_status === 0) {
        return false;
      } else {
        return true;
      }
    },
  },
};
</script>

<style></style>
