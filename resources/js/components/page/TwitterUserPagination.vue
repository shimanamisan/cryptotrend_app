<template>
  <div>
    <transition name="flash">
      <div class="u-flashmsg" v-show="flash_message_flg">
        <ul v-for="(msg, index) in this.systemMessage" :key="index">
          <li>{{ msg }}</li>
        </ul>
      </div>
    </transition>
    <section class="c-container c-container__twusr">
      <div class="p-twuser__header">
        <button
          class="c-btn c-btn__common c-btn__common--autofollow"
          :class="{ 'c-btn__disabled': !this.autoFollow_flg }"
          @click="sendAutoFollowRequest"
        >
          <p v-if="parseBoolean">自動フォロー機能ON</p>
          <p v-else>自動フォロー機能OFF</p>
        </button>
        <p class="p-twuser__header__text">
          {{ this.totalPage }}件中 {{ pageStart }} - {{ endPage }}件まで表示
        </p>
        <p class="p-twuser__header__text">
          {{ this.followcounter }}人フォロー済
        </p>
        <p>※1日に個別にフォロー/フォロー解除出来るのは20人が上限です</p>
      </div>
      <div
        class="p-twuser__card"
        v-for="(tw_userItems, index) in getTwitterUserItems"
        :key="index"
      >
        <template v-if="tw_userItems.isFollow">
          <button
            class="c-btn c-btn__common c-btn__common--unfollow"
            @click="sendUnFollowRequest(tw_userItems.id, index)"
          >
            フォロー解除する
          </button>
        </template>
        <template v-else>
          <button
            class="c-btn c-btn__common c-btn__common--follow"
            @click="sendFollowRequest(tw_userItems.id, index)"
          >
            フォローする
          </button>
        </template>
        <div class="p-twuser__detail__name">{{ tw_userItems.user_name }}</div>
        <div class="p-twuser__detail__account">
          ＠{{ tw_userItems.account_name }}
        </div>
        <div class="p-twuser__detail__description">
          {{ tw_userItems.description }}
        </div>
        <div class="p-twuser__detail__item">
          <p class="p-twuser__detail__item__count">
            フォロー数:{{ tw_userItems.friends_count }}
          </p>
          <p class="p-twuser__detail__item__count">
            フォロワー数:{{ tw_userItems.followers_count }}
          </p>
        </div>
        <div class="p-twuser__detail">
          <p class="p-twuser__detail__tweet__title">最新ツイート</p>
          <div class="p-twuser__detail__tweet">
            {{ tw_userItems.new_tweet }}
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
      :prev-text="'＜'"
      :next-text="'＞'"
      :containerClass="'c-pagination'"
      :page-class="'c-pagination__item'"
      :page-link-class="'c-pagination__link'"
      :prev-class="'c-pagination__item'"
      :prev-link-class="'c-pagination__link'"
      :next-class="'c-pagination__item'"
      :next-link-class="'c-pagination__link'"
      :active-class="'c-pagination__item--active'"
      :hide-prev-next="true"
    ></paginate>
  </div>
</template>

<script>
import Vue from 'vue';
import Paginate from 'vuejs-paginate';
// Vue.component('paginate', Paginate);
export default {
  props: ['follow_list', 'total_page', 'user'],
  components: {
    Paginate,
  },
  data() {
    return {
      totalPage: this.total_page,
      parPage: '',
      currentPage: 1,
      // 登録後のメッセージ表示フラグ
      flash_message_flg: false,
      systemMessage: '',
      // 自動フォロー中のフラグ(ユーザー情報より取得)
      autoFollow_flg: this.user.autofollow_status,
      followcounter: 0,
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
      this.parPage = Math.ceil(this.totalPage / 8);
    },
    scrollTop() {
      window.scrollTo({
        top: 0,
        behavior: 'auto',
      });
    },
    isShowMessage() {
      this.flash_message_flg = !this.flash_message_flg;
    },
    isAlreadyUserMessage() {
      this.already_follow_user = !this.already_follow_user;
    },
    async sendAutoFollowRequest() {
      // catch(error => error.response || error)で非同期通信が成功しても失敗してもresponseに結果を代入する
      const response = await axios
        .post('/autofollow', { status: this.autoFollow_flg })
        .catch((error) => error.response || error);

      if (this.autoFollow_flg == 0) {
        this.autoFollow_flg = 1;
      } else {
        this.autoFollow_flg = 0;
      }
      console.log(response);
    },
    async sendFollowRequest(id, index) {
      // catch(error => error.response || error)で非同期通信が成功しても失敗してもresponseに結果を代入する
      const response = await axios
        .post('/follow', { id: id })
        .catch((error) => error.response || error);

      if (response.status === 200) {
        // 通信が成功した時の処理

        // 返却されたメッセージを格納
        this.systemMessage = response.data;
        // フラッシュメッセージを表示
        this.isShowMessage();
        // 2秒後にメッセージを非表示にする
        setTimeout(this.isShowMessage, 2000);
        // フォロー済みのステータスを通知する
        this.$emit('is-follow', id);

      } else if (response.status === 403) {
        // ユーザーを既にフォローしていた時の処理
        this.systemMessage = response.data;
        this.isShowMessage();
        setTimeout(this.isShowMessage, 2000);

      } else {
        // 何か予期せぬErrorが発生したとき(500エラーなど)
        this.systemMessage = response.data;
        this.isShowMessage();
        setTimeout(this.isShowMessage, 2000);
      }
    },
    async sendUnFollowRequest(id, index) {
      // catch(error => error.response || error)で非同期通信が成功しても失敗してもresponseに結果を代入する
      const response = await axios
        .post('/unfollow', { id: id })
        .catch((error) => error.response || error);
      // 通信が成功した時の処理
      if (response.status === 200) {
        // 返却されたメッセージを格納
        this.systemMessage = response.data;
        // フラッシュメッセージを表示
        this.isShowMessage();
        // 2秒後にメッセージを非表示にする
        setTimeout(this.isShowMessage, 2000);
        // フォロー済みのステータスを通知する
        this.$emit('is-unfollow', id);

      } else if (response.status === 403) {
        this.systemMessage = response.data;
        this.isShowMessage();
        setTimeout(this.isShowMessage, 2000);

      } else {
        // 何か予期せぬErrorが発生したとき(500エラーなど)
        this.systemMessage = response.data;
        this.isShowMessage();
        setTimeout(this.isShowMessage, 2000);
      }
    },
    followUserCounter() {
      let counter = this.follow_list.filter((item) => {
        return item.isFollow;
      });
      this.followcounter = counter.length;
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

<style></style>
