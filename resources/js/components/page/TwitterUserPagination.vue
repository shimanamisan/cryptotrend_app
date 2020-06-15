<template>
  <div>
    <paginate
      v-model="currentPage"
      :page-count="getTwitterUserItems"
      :page-range="10"
      :margin-pages="2"
      :click-handler="clickCallback"
      :prev-text="'Prev'"
      :next-text="'Next'"
      :containerClass="'pagination'"
      :page-class="'page-item'"
      :page-link-class="'page-link'"
      :prev-class="'page-item'"
      :prev-link-class="'page-link'"
      :next-class="'page-item'"
      :next-link-class="'page-link'"
    ></paginate>
    <section class="c-container c-container__news">
      <div class="p-news__card" v-for="(tw_userItems, index) in getTwitterUserItems" :key="index">
        <p>{{ tw_userItems.account_name }}</p>
        <p>{{ tw_userItems.description }}</p>
        <p>{{ tw_userItems.new_tweet }}</p>
        <p>{{ tw_userItems.account_name }}</p>
        <br />
        <p>投稿日：{{ tw_userItems.created_at }}</p>
      </div>
    </section>
  </div>
</template>

<script>
import Vue from "vue";
import Paginate from "vuejs-paginate";
Vue.component("paginate", Paginate);
export default {
    data() {
        return {
            tw_userItems: this.tw_user,
            totalPage: this.total_page,
            parPage: "",
            currentPage: 1,
        };
    },
    props: ["tw_user", "total_page"],
    methods: {
        clickCallback(pageNum) {
            this.currentPage = pageNum;
        },
        paginationNumber() {
            // 表示するページネーションの数を割り出すために、ニュースの総数を表示させる数で割っている
            this.parPage = Math.ceil(this.totalPage / 40);
        },
    },
    computed: {
        getTwitterUserItems() {
            let current = this.currentPage * this.parPage;
            let start = current - this.parPage;
            console.log(
                "カレントページ：" + current + "スタートページ：" + start
            );

            return this.tw_userItems.slice(start, current);
        },
        getPageCount() {
            return Math.ceil(this.tw_userItems.length / this.parPage);
        },
    },
    created() {
        this.paginationNumber();
    },
};
</script>

<style>
.pagination {
  display: flex;
  margin: 0 auto;
  width: 80%;
  justify-content: center;
}
.page-item {
  background-color: aqua;
  padding: 10px 15px;
  margin-left: 12px;
  position: relative;
  overflow: hidden;
}
/* .active {
  background-color: brown;
} */
</style>
