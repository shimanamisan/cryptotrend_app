<template>
  <main class="l-main l-main__common">
    <h1 class="p-news__title">Twitterユーザー</h1>

    <Pagination :tw_user="this.tw_user" :total_page="this.totalPageNum" />
  </main>
</template>

<script>
import Pagination from './page/TwitterUserPagination';
export default {
    data(){
        return{
           totalPageNum:''

        }
    },
    props:['tw_user'],
    components:{
        Pagination
    },
    methods:{
        getTwitterUserLength(){
            // Twitterユーザーの総数を割り出す
            this.totalPageNum = this.tw_user.length;
        }
    },
    created(){
        this.getTwitterUserLength();
    }
}
</script>

<style>
</style>