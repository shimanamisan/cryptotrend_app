<template>
    <div class="p-modal">
        <div class="p-modal__container">
            <div class="p-modal__body">
                <div class="p-modal__nav">
                    <i
                        class="fas fa-times p-modal__nav--close"
                        @click="closeTrigger"
                    ></i>
                </div>
                <div class="p-modal__body__section">
                    <h3></h3>

                    <div class="p-mypage__content--inwrap">
                        <div class="p-mypage__content--cancel">
                            <button
                                class="c-btn p-mypage__btn p-mypage__btn--cancel"
                            >
                                入力をクリア
                            </button>
                        </div>
                        <div class="p-mypage__content--submit">
                            <button
                                class="c-btn p-mypage__btn p-mypage__btn--submit"
                                @click="confirmPassword"
                            >
                                変更を保存
                            </button>
                        </div>
                    </div>
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
            // 入力フォームデータバインディング
            userDataForm: {
                name: this.user,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation,
            },
        };
    },
    props: ["autofollow_status"],
    methods: {
        confirmPassword(){
            console.log('change!')
        },
        closeTrigger() {
            this.$emit("close-event");
        },
        sendAutoFollowTrigger() {
            this.$emit("autofollow-event");
            this.sending = !this.sending;
        },
        // axios通信完了時に親コンポーネントからこのメソッドを呼び出す
        sendingHandler() {
            this.sending = !this.sending;
            this.$emit("close-event");
        },
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
