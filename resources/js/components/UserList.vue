<template>
    <main class="l-main l-main__common">
        <h1 class="c-title c-title__twuser">関連アカウント一覧</h1>
        <Pagination
            :follow_list="this.follow_list_item"
            :user="this.user"
            :total_page="this.totalPageNum"
            @is-follow="addFollowState"
            @is-unfollow="removeFollowState"
        />
    </main>
</template>

<script>
import Pagination from "./page/TwitterUserPagination";

export default {
    data() {
        return {
            follow_list_item: this.follow_list,
            totalPageNum: "",
        };
    },
    props: ["follow_list", "user"],
    components: {
        Pagination,
    },
    methods: {
        getTwitterUserLength() {
            // Twitterユーザーの総数を割り出す
            this.totalPageNum = this.follow_list.length;
        },
        // フォロー済みか否か判定するプロパティを追加する
        addFollowState(id) {
            let data = this.follow_list_item;
            this.follow_list_item = data.map((item) => {
                if (item.id === id) {
                    return Object.assign({}, item, { isFollow: true });
                }
                return item;
            });
        },
        // フォローしていないユーザーか否か判定するプロパティを追加
        removeFollowState(id) {
            let data = this.follow_list_item;
            this.follow_list_item = data.map((item) => {
                if (item.id === id) {
                    return Object.assign({}, item, { isFollow: false });
                }
                return item;
            });
        },
        modalOpen() {
            this.open = !this.open;
        },
        modalClose() {
            this.open = !this.open;
        },
    },
    created() {
        this.getTwitterUserLength();
    },
};
</script>
