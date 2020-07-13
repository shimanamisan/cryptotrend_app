<template>
  <main class="l-main l-main__common">
    <h1 class="p-twuser__title">Twitterユーザー</h1>

    <Pagination :follow_list="this.follow_list" :user="this.user" :total_page="this.totalPageNum" />
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
    props:['follow_list', 'user', 'follow_list'],
    components:{
        Pagination
    },
    methods:{
        getTwitterUserLength(){
            // Twitterユーザーの総数を割り出す
            this.totalPageNum = this.follow_list.length;
        }
    },
    created(){
        this.getTwitterUserLength();
    }
}
</script>

<style>
</style>