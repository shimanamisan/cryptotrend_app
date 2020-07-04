<template>
  <div>
    <div class="l-main l-main__common">
      <h1 class="p-coin__title">トレンドランキング</h1>
      <section class="c-container c-container__coin">
        <div class="p-coin__header">
          <p class="p-coin__header__title">※チェックすると銘柄で絞り込みが出来ます</p>

            <CoinSearch/>

          <ul class="p-coin__list p-coin__list__btn">
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common" @click="getHourCoins">
                過去1時間
              </button>
            </li>
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common" @click="getDayCoins">
                過去1日
              </button>
            </li>
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common" @click="getWeekCoins">
                過去1週間
              </button>
            </li>
          </ul>
          
          <div class="p-coin__list p-coin__list__btn p-coin__update_at u-margin__bottom--ss">
            <span>更新日時:{{ getUpdated }}</span>
          </div>
        </div>
        <div class="p-coin__table">
          <table class="p-coin__table--inner" border="3">
            <tr>
              <th class="">RANKING</th>
              <th>銘柄</th>
              <th>ツイート数</th>
              <th>最高取引価格（24H）</th>
              <th>最安取引価格（24H）</th>
            </tr>
            <!-- <tr v-for="(coin, index) in getprice" :key="index"> -->
            <tr v-for="(coin, index) in this.coin_data" :key="index">
              <td>{{ index + 1 }}</td>
              <td><a class="p-coin__table__link" :href="serch_url+coin.coin_name">{{ coin.coin_name }}</a></td>
              <td>{{ coin.tweet }}</td>
              <template v-if="coin.max_price == 0">
                <td>不明</td>
              </template>
              <template v-else>
                <td>{{ coin.max_price }}</td>
              </template>
               <template v-if="coin.low_price == 0">
                <td>不明</td>
              </template>
              <template v-else>
                <td>{{ coin.low_price }}</td>
              </template>
            </tr>
          </table>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import CoinSearch from './CoinSearch'
export default {
  data(){
    return{
      coin_data: [],
      serch_url: 'https://twitter.com/search?q='
    }
  },
  components: {
    CoinSearch
  },
  methods:{
    // 過去1時間のツイート数をDBから取得
    async getHourCoins(){
      await axios.get('/coins/hour').then(response => {
        this.coin_data = response.data
        // ツイート数の多い順に並び替える
        let sort_data = this.coin_data.sort((a,b) => {
            return b.tweet - a.tweet
        })
        this.coin_data = sort_data
        
      }).catch(error => {
        alert('エラーが発生しました。しばらくしてから、再度アクセスして下さい。')
      })
    },
    // 過去1日のツイート数をDBから取得
    async getDayCoins(){
      await axios.get('/coins/day').then(response => {
        this.coin_data = response.data
        // ツイート数の多い順に並び替える
        let sort_data = this.coin_data.sort((a,b) => {
            return b.tweet - a.tweet
        })
        this.coin_data = sort_data
      }).catch(error => {
        alert('エラーが発生しました。しばらくしてから、再度アクセスして下さい。')
      })
    },
    // 過去1週間のツイート数をDBから取得
    async getWeekCoins(){
      await axios.get('/coins/week').then(response => {
        this.coin_data = response.data
        // ツイート数の多い順に並び替える
        let sort_data = this.coin_data.sort((a,b) => {
            return b.tweet - a.tweet
        })
        this.coin_data = sort_data
      }).catch(error => {
        alert('エラーが発生しました。しばらくしてから、再度アクセスして下さい。')
      })
    },
  },
  computed:{
    getUpdated(){
      // 配列の個数を取得
      const latest_data = this.coin_data.length
      // 配列の中の最後尾のデータが最も更新日時が新しいので、その日時を取得する
      var filter_data =  this.coin_data.filter( (item, index) => {
          // 要素のIDと比較して一致していれば、その要素1つを返す
            return item.id == latest_data
      })
      // 更新日時を格納した新しい配列を生成する
      var result = filter_data.map(element => {
          return element.updated_at
        });

      return result[0]
    }
  },
  created(){
    this.getHourCoins()
  }
};
</script>

<style></style>
