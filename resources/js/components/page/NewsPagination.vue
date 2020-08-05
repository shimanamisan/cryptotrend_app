<template>
    <div>
        <section class="c-container c-container__news">
            <div
                class="p-news__card"
                v-for="(news, index) in getnewsItems"
                :key="index"
            >
                <a
                    class="p-news__link"
                    :href="news.url"
                    target="_blank"
                    rel="noopener"
                    ><p>{{ news.title }}</p></a
                >
                <br />
                <p>投稿日：{{ news.pubDate }}</p>
            </div>
        </section>
        <paginate
            v-model="currentPage"
            :page-count="getPageCount"
            :page-range="10"
            :margin-pages="2"
            :click-handler="clickCallback"
            :prev-text="'＜'"
            :next-text="'＞'"
            :containerClass="'c-pagination'"
            :page-class="'c-pagination__item'"
            :page-link-class="'c-pagination__link'"
            :prev-class="'c-pagination__item c-pagination__item--prev'"
            :prev-link-class="'c-pagination__link'"
            :next-class="'c-pagination__item c-pagination__item--next'"
            :next-link-class="'c-pagination__link'"
            :active-class="'c-pagination__item--active'"
            :hide-prev-next="true"
        ></paginate>
    </div>
</template>

<script>
import Vue from "vue";
import Paginate from "vuejs-paginate";
Vue.component("paginate", Paginate);
export default {
    data() {
        return {
            newsItems: this.news_data,
            totalPage: this.total_page,
            parPage: "",
            currentPage: 1,
        };
    },
    props: ["news_data", "total_page"],
    /********************************
     * メソッド
     ********************************/
    methods: {
        clickCallback(pageNum) {
            this.currentPage = pageNum;
            this.scrollTop();
        },
        paginationNumber() {
            // 表示するページネーションの数を割り出すために、ニュースの総数を表示させる数で割っている
            this.parPage = this.totalPage / 10;
        },
        scrollTop() {
            window.scrollTo({
                top: 0,
                behavior: "auto",
            });
        },
    },
    /********************************
     * 算出プロパティ
     ********************************/
    computed: {
        getnewsItems() {
            let current = this.currentPage * this.parPage;
            let start = current - this.parPage;
            return this.newsItems.slice(start, current);
        },
        getPageCount() {
            return Math.ceil(this.newsItems.length / this.parPage);
        },
    },
    created() {
        this.paginationNumber();
    },
};
</script>
