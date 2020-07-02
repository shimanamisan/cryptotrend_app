<template>
  <div>
    <div class="l-main l-main__common">
      <h1 class="p-coin__title">トレンドランキング</h1>
      <section class="c-container c-container__coin">
        <div class="p-coin__header">
          <p class="p-coin__header__title">※チェックすると銘柄で絞り込みが出来ます</p>
          <ul class="p-coin__list">
            <li class="p-coin__item c-btn__common">
              <label for="BTC">
                <input type="checkbox" id="BTC">
                <span>BTC</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="ETH">
                <input type="checkbox" id="ETH">
                <span>ETH</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="ETC">
                <input type="checkbox" id="ETC">
                <span>ETC</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="LSK">
                <input type="checkbox" id="LSK">
                <span>LSK</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="FCT">
                <input type="checkbox" id="FCT">
                <span>FCT</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="XRP">
                <input type="checkbox" id="XRP">
                <span>XRP</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="XEM">
                <input type="checkbox" id="XEM">
                <span>XEM</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="BCT">
                <input type="checkbox" id="BCT">
                <span>BCT</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="MONA">
                <input type="checkbox" id="MONA">
                <span>MONA</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="XLM">
                <input type="checkbox" id="XLM">
                <span>XLM</span>
              </label>
              </li>
            <li class="p-coin__item c-btn__common">
              <label for="QTUM">
                <input type="checkbox" id="QTUM">
                <span>QTUM</span>
              </label>
              </li>
          </ul>
          <ul class="p-coin__list p-coin__list__btn">
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common">
                過去1時間
              </button>
            </li>
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common">
                過去1日
              </button>
            </li>
            <li class="p-coin__item__btn">
              <button class="c-btn c-btn__common">
                過去1週間
              </button>
            </li>

          </ul>
          <div class="p-coin__list p-coin__list__btn p-coin__update_at">
            <span>更新日時:2020-xx-xx 04:zz:xx</span>
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
            <tr v-for="(coin, index) in coin_data" :key="index">
              <td>{{ index + 1 }}</td>
              <td><a class="p-coin__table__link" :href="serch_url+coin.coin_name">{{ coin.coin_name }}</a></td>
              <td>{{ coin.hour }}</td>
              <td>{{ coin.low_price }}</td>
              <td>{{ coin.max_price }}</td>
            </tr>
            <!-- <tr>
              <td>2</td>
              <td>イーサリアム</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>3</td>
              <td>イーサリアムクラシック</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>4</td>
              <td>リスク</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>5</td>
              <td>ファクトム</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>6</td>
              <td>リップル</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>7</td>
              <td>ネム</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>8</td>
              <td>ライトコイン</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>9</td>
              <td>ビットコインキャッシュ</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>10</td>
              <td>モナコイン</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>11</td>
              <td>ステラルーメン</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>12</td>
              <td>クアンタム</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr> -->
          </table>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  data(){
    return{
      coin_data: '',
      serch_url: 'https://twitter.com/search?q='
    }
  },
  // props: ['coins'],
  methods:{
    async getHourCoins(){
      await axios.get('/coins/trend').then(response => {
        this.coin_data = response.data
      }).catch(error => {
        alert('エラーが発生しました。しばらくしてから、再度アクセスして下さい。')
      })
    }
  },
  computed:{},
  created(){
    this.getHourCoins()
  }
};
</script>

<style></style>
