<template>
    <div>
        <div class="l-main l-main__common">
            <Loading v-show="loading" />
            <h1 class="c-title c-title__coins">トレンドランキング</h1>
            <section class="c-container c-container__coin">
                <div class="p-coin__header">
                    <CoinSearch
                        :coin-data="this.coni_data"
                        @check-event="checkCoins"
                        @clear-event="clearSearch"
                    />

                    <ul class="p-coin__list p-coin__list__btn">
                        <li>
                            <button
                                class="c-btn c-btn__common p-coin__item__btn p-coin__item__time"
                                :class="{
                                    'p-coin__item__btn--false': !isActive_hour,
                                }"
                                @click="getHourCoins"
                            >
                                過去1時間
                            </button>
                        </li>
                        <li>
                            <button
                                class="c-btn c-btn__common p-coin__item__btn p-coin__item__time"
                                :class="{
                                    'p-coin__item__btn--false': !isActive_day,
                                }"
                                @click="getDayCoins"
                            >
                                過去1日
                            </button>
                        </li>
                        <li>
                            <button
                                class="c-btn c-btn__common p-coin__item__btn p-coin__item__time"
                                :class="{
                                    'p-coin__item__btn--false': !isActive_week,
                                }"
                                @click="getWeekCoins"
                            >
                                過去1週間
                            </button>
                        </li>
                    </ul>
                    <div
                        class="p-coin__list p-coin__update_at u-margin__bottom--m"
                    >
                        <span>更新日時:{{ getUpdated }}</span>
                    </div>
                    <div
                        class="p-coin__list p-coin__list__nav u-margin__bottom--ss"
                    >
                        <span
                            >※左右にスワイプすると詳細な情報を見ることが出来ます</span
                        >
                    </div>
                </div>
                <div class="p-coin__table__wrapp">
                    <div class="p-coin__table">
                        <table class="p-coin__table--inner" border="3">
                            <tr>
                                <th class="">RANKING</th>
                                <th>銘柄</th>
                                <th>ツイート数</th>
                                <th>最高取引価格（24H）</th>
                                <th>最安取引価格（24H）</th>
                            </tr>
                            <tr
                                v-for="(coin, index) in filterCoins"
                                :key="index"
                            >
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <a
                                        class="p-coin__table__link"
                                        :href="search_url + coin.coin_name"
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
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import CoinSearch from "./CoinSearch";
import Loading from "./module/Loading";
export default {
    data() {
        return {
            coni_data: [
                {
                    abbreviation: "BTC",
                    value: "BTC（ビットコイン）",
                },
                {
                    abbreviation: "ETH",
                    value: "ETH（イーサリアム）",
                },
                {
                    abbreviation: "ETC",
                    value: "ETC（イーサリアムクラシック）",
                },
                {
                    abbreviation: "LSK",
                    value: "LSK（リスク）",
                },
                {
                    abbreviation: "FCT",
                    value: "FCT（ファクトム）",
                },
                {
                    abbreviation: "XRP",
                    value: "XRP（リップル）",
                },
                {
                    abbreviation: "XEM",
                    value: "XEM（ネム）",
                },
                {
                    abbreviation: "LTC",
                    value: "LTC（ライトコイン）",
                },
                {
                    abbreviation: "BCH",
                    value: "BCH（ビットコインキャッシュ）",
                },
                {
                    abbreviation: "MONA",
                    value: "MONA（モナコイン）",
                },
                {
                    abbreviation: "XLM",
                    value: "XLM（ステラルーメン）",
                },
                {
                    abbreviation: "QTUM",
                    value: "QTUM（クアンタム）",
                },
            ],
            tweet_data: [], // トレンド表示用データ
            search_value: [], // チェックボックスで絞り込む為のデータ
            search_url: "https://twitter.com/search?q=", // Twitterへのリンク
            isActive_hour: false, // hour、day、weekのツイートなのか判断する
            isActive_day: false, // hour、day、weekのツイートなのか判断する
            isActive_week: false, // hour、day、weekのツイートなのか判断する
            loading: false, // 非同期通信時ローディングを表示する
        };
    },
    components: {
        CoinSearch,
        Loading,
    },
    /********************************
     * メソッド
     ********************************/
    methods: {
        // 子コンポーネントからチェックボックスの値を受け取り格納する
        checkCoins(value) {
            this.search_value = value;
        },
        clearSearch() {
            this.search_value = [];
        },
        // サーバーから受け取った値をソートする
        addProperty(response) {
            this.tweet_data = response.data;
            // ツイート数の多い順に並び替える
            this.tweet_data = this.tweet_data.sort((a, b) => {
                return b.tweet - a.tweet;
            });
        },
        // 過去1時間のツイート数をDBから取得
        async getHourCoins() {
            const HOUR = "hour";
            this.loadingActive();
            await axios
                .get("/coins/hour")
                .then((response) => {
                    this.addProperty(response);
                    this.changeCoinFlg(HOUR);
                    this.loadingActive();
                })
                .catch((error) => {
                    this.loadingActive();
                    alert(
                        "エラーが発生しました。しばらくしてから、再度アクセスして下さい。"
                    );
                });
        },
        // 過去1日のツイート数をDBから取得
        async getDayCoins() {
            const DAY = "day";
            this.loadingActive();
            await axios
                .get("/coins/day")
                .then((response) => {
                    this.addProperty(response);
                    this.changeCoinFlg(DAY);
                    this.loadingActive();
                })
                .catch((error) => {
                    this.loadingActive();
                    alert(
                        "エラーが発生しました。しばらくしてから、再度アクセスして下さい。"
                    );
                });
        },
        // 過去1週間のツイート数をDBから取得
        async getWeekCoins() {
            const WEEK = "week";
            this.loadingActive();
            await axios
                .get("/coins/week")
                .then((response) => {
                    this.addProperty(response);
                    this.changeCoinFlg(WEEK);
                    this.loadingActive();
                })
                .catch((error) => {
                    this.loadingActive();
                    alert(
                        "エラーが発生しました。しばらくしてから、再度アクセスして下さい。"
                    );
                });
        },
        changeCoinFlg(coin) {
            switch (coin) {
                case "hour":
                    this.isActive_hour = !this.isActive_hour;
                    this.isActive_day = false;
                    this.isActive_week = false;
                    break;
                case "day":
                    this.isActive_hour = false;
                    this.isActive_day = !this.isActive_day;
                    this.isActive_week = false;
                    break;
                case "week":
                    this.isActive_hour = false;
                    this.isActive_day = false;
                    this.isActive_week = !this.isActive_week;
                    break;
            }
        },
        loadingActive() {
            this.loading = !this.loading;
        },
    },
    /********************************
     * 算出プロパティ
     ********************************/
    computed: {
        // 各銘柄の最終更新日時を取得する
        getUpdated() {
            // 配列の個数を取得
            // 配列の先頭のデータを取得する
            var filter_data = this.tweet_data.filter((item, index) => {
                // 要素のIDと比較して一致していれば、その要素1つを返す
                return item.id == 1;
            });
            // 更新日時を格納した新しい配列を生成する
            var result = filter_data.map((element) => {
                return element.updated_at;
            });
            return result[0];
        },
        filterCoins() {
            var tweet_data = this.tweet_data; // DBから取得してきたデータ
            var search_value = this.search_value; // チェックボックスから渡ってきたvalueが格納された配列

            // チェックボックスがチェックされていたら実行
            if (search_value.length > 0) {
                // 配列の中のデータを分解
                return tweet_data.filter((item) => {
                    // チェックボックスの値と同じものがあれば、その要素を新しい配列として返却
                    return this.search_value.indexOf(item.coin_name) > -1;
                });
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
