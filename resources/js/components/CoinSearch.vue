<template>
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
