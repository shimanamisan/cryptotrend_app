<template>
  <div>
    <div class="l-main l-main__common">
      <h1 class="p-coin__title">トレンドランキング</h1>
      <section class="c-container c-container__twusr">
        <div class="p-coin__boxWrapp">
          <ul class="p-coin__list">
            <li class="p-coin__item">BTC</li>
            <li class="p-coin__item">ETH</li>
            <li class="p-coin__item">ETC</li>
            <li class="p-coin__item">LSK</li>
            <li class="p-coin__item">FCT</li>
            <li class="p-coin__item">XRP</li>
            <li class="p-coin__item">XEM</li>
            <li class="p-coin__item">BCT</li>
            <li class="p-coin__item">MONA</li>
            <li class="p-coin__item">XLM</li>
            <li class="p-coin__item">QTUM</li>
          </ul>
          <ul class="p-coin__list">
            <li class="p-coin__item">過去1時間</li>
            <li class="p-coin__item">過去1日</li>
            <li class="p-coin__item">過去1週間</li>
          </ul>
          <div class="p-coin__update_at">
            <span>更新日時:2020-xx-xx 04:zz:xx</span>
          </div>
        </div>
        <div class="p-coin__table">
          <table class="p-coin__table--inner" border="3">
            <tr>
              <th class="">RANKING</th>
              <th>Crypto Coin Name</th>
              <th>Highest price</th>
              <th>Cheapest price</th>
            </tr>
            <tr>
              <td>1</td>
              <td>ビットコイン</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>2</td>
              <td>イーサリアム</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>3</td>
              <td>イーサリアムクラシック</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>4</td>
              <td>リスク</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>5</td>
              <td>ファクトム</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>6</td>
              <td>リップル</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>7</td>
              <td>ネム</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>8</td>
              <td>ライトコイン</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>9</td>
              <td>ビットコインキャッシュ</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>10</td>
              <td>モナコイン</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>11</td>
              <td>ステラルーメ</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
            <tr>
              <td>12</td>
              <td>クアンタム</td>
              <td>10000</td>
              <td>1000</td>
            </tr>
          </table>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  props: ['coins'],
};
</script>

<style></style>
