<template>
  <main class="l-main l-main__common">
    <h1 class="p-news__title">仮想通貨ニュース</h1>
    <section class="c-container c-container__news">
      <div class="p-news__card" v-for="(news, index) in news_data" :key="index">
        <p>{{ news.title }}</p>

        <br />
        <p>投稿日：{{ news.pubDate}}</p>
      </div>
      <div class="p-news__card">
        <p>
          米株式市場が史上最高値記録でビットコインのパフォーマンスを上回る、コロナショックを乗り越え急進
          - 株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Tue, 09 Jun 202003:58:00 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          次回FATF会議は6月24日開催、仮想通貨事業の新基準対応評価など -
          株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Mon, 08 Jun 2020 07:53:08 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          中国で仮想通貨トレーダーの銀行口座凍結事例 資金洗浄捜査が影響か -
          株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Mon, 08 Jun 2020 22:54:13 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          仮想通貨ビットコインが上昇トレンドにある3つの理由とは -
          コインテレグラフ・ジャパン（ビットコイン、仮想通貨、ブロックチェーンのニュース）
        </p>
        <br />
        <p>投稿日：Mon, 08 Jun 2020 09:53:55 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          【仮想通貨初心者向け】ビットフライヤーでビットコイン投資を始める方法：bitFlyer
          Lightning編 | 仮想通貨コラム | 仮想通貨の比較・ランキングならHEDGE -
          金融・投資情報メディア HEDGE GUIDE
        </p>
        <br />
        <p>投稿日：Tue, 09 Jun 2020 12:21:47 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          米株式市場が史上最高値記録でビットコインのパフォーマンスを上回る、コロナショックを乗り越え急進
          - 株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Tue, 09 Jun 202003:58:00 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          米株式市場が史上最高値記録でビットコインのパフォーマンスを上回る、コロナショックを乗り越え急進
          - 株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Tue, 09 Jun 202003:58:00 GMT</p>
      </div>
      <div class="p-news__card">
        <p>
          米株式市場が史上最高値記録でビットコインのパフォーマンスを上回る、コロナショックを乗り越え急進
          - 株式会社CoinPost
        </p>
        <br />
        <p>投稿日：Tue, 09 Jun 202003:58:00 GMT</p>
      </div>
    </section>
  </main>
</template>

<script>
export default {
    data(){
        return{
            // newsItem: this.newsData
        }
    },
    // js内ではキャメルケース
    props: ['news_data'],
    // methods:{
    //     log(){
    //         console.log(newsData)
    //     }
    // }
}
</script>

<style>
</style>