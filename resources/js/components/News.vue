<template>
    <main class="l-main l-main__common">
        <h1 class="c-title">仮想通貨ニュース</h1>
        <div>
            <Pagination
                :news_data="this.news_data"
                :total_page="this.totalPageNum"
            />
        </div>
    </main>
</template>

<script>
import Pagination from "./page/NewsPagination";
export default {
    data() {
        return {
            totalPageNum: "",
            newsData: "",
        };
    },
    components: {
        Pagination,
    },
    // js内ではキャメルケース
    props: ["news_data"],
    methods: {
        getNewsLength() {
            // ニュースの記事の総数を取得。ページネーションコンポーネントにpropsで渡す
            this.totalPageNum = this.news_data.length;
        },
    },
    created() {
        this.getNewsLength();
    },
};
</script>
