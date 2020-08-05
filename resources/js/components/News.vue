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
};
</script>
