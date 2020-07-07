<template>
  <div>
    <div class="l-main l-main__common">
      <h1 class="p-coin__title">トレンドランキング</h1>
      <section class="c-container c-container__coin">
        <div class="p-coin__header">
          <p class="p-coin__header__title">
            ※チェックすると銘柄で絞り込みが出来ます
          </p>

          <CoinSearch :coin-data="this.coni_data" @check-vent="checkCoins" />

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

          <div
            class="p-coin__list p-coin__list__btn p-coin__update_at u-margin__bottom--ss"
          >
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
            <tr
              v-for="(coin, index) in filterCoins"
              :key="index"
              v-show="coin.display"
            >
              <td>{{ index + 1 }}</td>
              <td>
                <a
                  class="p-coin__table__link"
                  :href="serch_url + coin.coin_name"
                  >{{ coin.coin_name }}</a
                >
              </td>
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
import CoinSearch from './CoinSearch';
export default {
  data() {
    return {
      coni_data: [
        {
          abbreviation: 'BTC',
          value: 'ビットコイン',
        },
        {
          abbreviation: 'ETH',
          value: 'イーサリアム',
        },
        {
          abbreviation: 'ETC',
          value: 'イーサリアムクラシック',
        },
        {
          abbreviation: 'LSK',
          value: 'リスク',
        },
        {
          abbreviation: 'FCT',
          value: 'ファクトム',
        },
        {
          abbreviation: 'XRP',
          value: 'リップル',
        },
        {
          abbreviation: 'XEM',
          value: 'ネム',
        },
        {
          abbreviation: 'LTC',
          value: 'ライトコイン',
        },
        {
          abbreviation: 'BCH',
          value: 'ビットコインキャッシュ',
        },
        {
          abbreviation: 'MONA',
          value: 'モナコイン',
        },
        {
          abbreviation: 'XLM',
          value: 'ステラルーメン',
        },
        {
          abbreviation: 'QTUM',
          value: 'クアンタム',
        },
      ],
      tweet_data: [], // トレンド表示用データ
      serch_value: [], // チェックボックスで絞り込む為のデータ
      serch_url: 'https://twitter.com/search?q=', // Twitterへのリンク
    };
  },
  components: {
    CoinSearch,
  },
  methods: {
    // 子コンポーネントからチェックボックスの値を受け取り格納する
    checkCoins(value) {
      this.serch_value = value;
    },
    // サーバーから受け取った値をソートし、displayプロパティを追加する
    addProperty(response) {
      this.tweet_data = response.data;
      // ツイート数の多い順に並び替える
      let sort_data = this.tweet_data.sort((a, b) => {
        return b.tweet - a.tweet;
      });
      // displayプロパティを追加する
      var new_items = sort_data.map((item) => {
        return Object.assign({}, item, { display: true });
      });
      // displayプロパティの真偽値で絞り込み時の表示・非表示を切り替える
      this.tweet_data = new_items;
    },
    // 過去1時間のツイート数をDBから取得
    async getHourCoins() {
      await axios
        .get('/coins/hour')
        .then((response) => {
          this.addProperty(response);
        })
        .catch((error) => {
          alert(
            'エラーが発生しました。しばらくしてから、再度アクセスして下さい。'
          );
        });
    },
    // 過去1日のツイート数をDBから取得
    async getDayCoins() {
      await axios
        .get('/coins/day')
        .then((response) => {
          this.addProperty(response);
        })
        .catch((error) => {
          alert(
            'エラーが発生しました。しばらくしてから、再度アクセスして下さい。'
          );
        });
    },
    // 過去1週間のツイート数をDBから取得
    async getWeekCoins() {
      await axios
        .get('/coins/week')
        .then((response) => {
          this.addProperty(response);
        })
        .catch((error) => {
          alert(
            'エラーが発生しました。しばらくしてから、再度アクセスして下さい。'
          );
        });
    },
  },
  computed: {
    // 各銘柄の最終更新日時を取得する
    getUpdated() {
      // 配列の個数を取得
      const latest_data = this.tweet_data.length;
      // 配列の中の最後尾のデータが最も更新日時が新しいので、その日時を取得する
      var filter_data = this.tweet_data.filter((item, index) => {
        // 要素のIDと比較して一致していれば、その要素1つを返す
        return item.id == latest_data;
      });
      // 更新日時を格納した新しい配列を生成する
      var result = filter_data.map((element) => {
        return element.updated_at;
      });

      return result[0];
    },
    filterCoins() {
      var tweet_data = this.tweet_data; // DBから取得してきたデータ
      var serch_value = this.serch_value; // チェックボックスから渡ってきたvalueが格納された配列

      // チェックボックスがチェックされていたら実行
      if (serch_value.length > 0) {
        // 配列の中のデータを分解
        return tweet_data.filter((item) => {
          // チェックボックスの値と同じものがあれば、その要素を新しい配列として返却
          return this.serch_value.indexOf(item.coin_name) > -1;
        });

        // console.log(tweet_data.filter((item) => {
        //   // チェックボックスの値と同じものがあれば、その要素を返却
        //   return this.serch_value.indexOf(item.coin_name) > -1;
        // }))
      } else {
        // 何も選択されていない場合は、未処理のデータを返却
        return (this.tweet_data = tweet_data);
      }
    },
  },
  created() {
    this.getHourCoins();
  },
};
</script>

<style></style>
