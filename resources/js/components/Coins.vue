<template>
  <div>
    <div class="l-main l-main__common">
      <h1 class="p-coin__title">トレンドランキング</h1>
      <section class="c-container c-container__coin">
        <div class="p-coin__boxWrapp">
          <ul class="p-coin__list">
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>BTC</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>ETH</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>ETC</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>LSK</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>FCT</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>XRP</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>XEM</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>BCT</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>MONA</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>XLM</span>
              </label>
              </li>
            <li class="p-coin__item">
              <label for="">
                <input type="checkbox">
                <span>QTUM</span>
              </label>
              </li>
          </ul>
          <ul class="p-coin__list">
            <li class="p-coin__item">
              <button>
                過去1時間
              </button>
            </li>
            <li class="p-coin__item">
              <button>
                過去1日
              </button>
            </li>
            <li class="p-coin__item">
              <button>
                過去1週間
              </button>
            </li>

          </ul>
          <div class="p-coin__update_at">
            <span>更新日時:2020-xx-xx 04:zz:xx</span>
          </div>
        </div>
        <div class="p-coin__table">
          <table class="p-coin__table--inner" border="3">
            <tr>
              <th class="">RANKING</th>
              <th>銘柄</th>
              <th>ツイート数</th>
              <th>過去24時間 最高取引価格</th>
              <th>過去24時間 最安取引価格</th>
            </tr>
            <tr>
              <td>1</td>
              <td>ビットコイン</td>
              <td>10000</td>
              <td>1000</td>
              <td>1000</td>
            </tr>
            <tr>
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
