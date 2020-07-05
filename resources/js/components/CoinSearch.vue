<template>


    <ul class="p-coin__list u-margin__bottom--l">
      <li v-for="(data, index) in this.coinData" :key="index" class="p-coin__item c-btn__common">
        <label :for="data.abbreviation">
          <input type="checkbox" :id="data.abbreviation" :value="data.value" v-model="checkedNames" @change="checkTrigger" />
          <span>{{ data.abbreviation }}</span>
        </label>
      </li>
      <!-- <li class="p-coin__item c-btn__common">
        <label for="BTC">
          <input type="checkbox" id="BTC" value="ビットコイン" v-model="checkedNames" @change="triggerFunc" />
          <span>BTC</span>
        </label>
      </li> -->
      <!-- <li class="p-coin__item c-btn__common">
        <label for="ETH">
          <input type="checkbox" id="ETH" value="イーサリアム" v-model="checkedNames"/>
          <span>ETH</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="ETC">
          <input type="checkbox" id="ETC" value="イーサリアムクラシック" v-model="checkedNames"/>
          <span>ETC</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="LSK">
          <input type="checkbox" id="LSK" value="リスク" />
          <span>LSK</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="FCT">
          <input type="checkbox" id="FCT" value="ファクトム" />
          <span>FCT</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="XRP">
          <input type="checkbox" id="XRP" value="リップル" />
          <span>XRP</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="XEM">
          <input type="checkbox" id="XEM" value="ネム" />
          <span>XEM</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="BCT">
          <input type="checkbox" id="BCT" value="" />
          <span>BCT</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="MONA">
          <input type="checkbox" id="MONA" value="ビットコインキャッシュ" />
          <span>MONA</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="XLM">
          <input type="checkbox" id="XLM" value="ネム" />
          <span>XLM</span>
        </label>
      </li>
      <li class="p-coin__item c-btn__common">
        <label for="QTUM">
          <input type="checkbox" id="QTUM" value="" />
          <span>QTUM</span>
        </label>
      </li> -->
    </ul>

</template>

<script>
export default {
  data() {
    return {
      checkedNames: []
    };
  },
  props: ['coinData'],
  methods: {
    checkTrigger(e){
      // console.log(e.target.value)
      this.$emit('check-vent', this.checkedNames)
    }
  },
};
</script>

<style></style>
