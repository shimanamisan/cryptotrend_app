<template>
  <div>
    <section class="c-container c-container__twusr">
      <transition name="flash">
        <div v-show="flash_message">
          <div class="u-flashmsg" v-if="already_follow">
            <p>既にフォロー済みです</p>
          </div>
          <div class="u-flashmsg" v-else>
            <p>フォローしました！</p>
          </div>
        </div>
      </transition>
      <div class="p-twuser__header">
        <button class="c-btn c-btn__common c-btn__common--autofollow">自動フォロー機能</button>
        <p
          class="p-twuser__header__text"
        >{{ this.totalPage }}件中 {{ pageStart }} - {{ endPage }}件まで表示</p>
        <p class="p-twuser__header__text">○○人フォロー済</p>
      </div>
      <div class="p-twuser__card" v-for="(tw_userItems, index) in getTwitterUserItems" :key="index">
        <button
          class="c-btn c-btn__common c-btn__common--follow"
          @click="sendFollowRequest(tw_userItems.twitter_id, index)"
        >フォローする</button>
        <div class="p-twuser__detail__name">{{ tw_userItems.user_name }}</div>
        <div class="p-twuser__detail__account">＠{{ tw_userItems.account_name }}</div>
        <div class="p-twuser__detail__description">{{ tw_userItems.description }}</div>
        <div class="p-twuser__detail__item">
          <p class="p-twuser__detail__item__count">フォロー数:{{ tw_userItems.friends_count }}</p>
          <p class="p-twuser__detail__item__count">フォロワー数:{{ tw_userItems.followers_count }}</p>
        </div>
        <div class="p-twuser__detail">
          <p class="p-twuser__detail__tweet__title">最新ツイート</p>
          <div class="p-twuser__detail__tweet">{{ tw_userItems.new_tweet }}</div>
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
Vue.component('paginate', Paginate);
export default {
  props: ['tw_user', 'total_page'],
  data() {
    return {
      tw_userItems: this.tw_user,
      totalPage: this.total_page,
      parPage: '',
      currentPage: 1,
      // 登録後のメッセージ表示フラグ
      flash_message: false,
      already_follow: false,
    };
  },
  methods: {
    clickCallback(pageNum) {
      this.currentPage = pageNum;
      this.scrollTop();
    },
    paginationNumber() {
      // 表示するページネーションの数を割り出すために、総数を表示させる数で割っている
      this.parPage = Math.ceil(this.totalPage / 5);
    },
    scrollTop() {
      window.scrollTo({
        top: 0,
        behavior: 'auto',
      });
    },
    async sendFollowRequest(id, index) {
      console.log('twitter_id：' +id + '、 インデックス番号：' + index)
      
      await axios.post('/follow', { id : id }).then(response => {
        // 通信が成功した時の処理
        this.tw_userItems.splice(index, 1);
        this.flash_message = true
      }).catch(error => {
        console.log(error)
        this.flash_message = true
        this.already_follow = true;
      })

    


      // const i = tw_userItems.splice();

    },
  },
  computed: {
    // 表示させる要素を切り出す
    getTwitterUserItems() {
      let current = this.currentPage * this.parPage;
      let start = current - this.parPage;
      // console.log(start + '件から' + current + '件まで表示中');

      // sliceで配列を切り取る。index番号で指定するのでstartは0番から始まる
      return this.tw_userItems.slice(start, current);
    },
    getPageCount() {
      // 取得してきたユーザー情報の総数 ÷ 表示させるページネーション数
      return Math.ceil(this.tw_userItems.length / this.parPage);
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
  },
  created() {
    this.paginationNumber();
  },
};
</script>

<style>
.flash-enter-active,
.flash-leave-active {
  transition: all 0.6s ease;
}
.flash-enter,
.flash-leave-to {
  opacity: 0;
  transform: translateX(50px);
}
</style>
