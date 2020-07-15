<template>
  <div>
    <div class="p-coin__header__container">
      <p class="p-coin__header__title">
        ※銘柄をチェックすると、絞り込みが出来ます
      </p>
      <button class="c-btn__common p-coin__item p-coin__item__btn p-coin__item__btn--clear">絞り込みをクリア</button>
    </div>
    <ul class="p-coin__list u-margin__bottom--l">
      <li
        v-for="(data, index) in this.coinData"
        :key="index"
        class="p-coin__item c-btn__common"
      >
        <label :for="data.abbreviation">
          <input
            type="checkbox"
            :id="data.abbreviation"
            :value="data.value"
            v-model="checkedNames"
            @change="checkTrigger"
          />
          <span>{{ data.abbreviation }}</span>
        </label>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      checkedNames: [],
    };
  },
  props: ['coinData'],
  methods: {
    checkTrigger(e) {
      // console.log(e.target.value)
      this.$emit('check-vent', this.checkedNames);
    },
  },
};
</script>

<style></style>
