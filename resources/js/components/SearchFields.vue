<template>
  <div>
    <b-form inline>
      <b-row class="mt-4">
        <b-col cols="3">
          <b-form-select
            v-model="selectedCountry"
            :options="countriesList"
          ></b-form-select>
        </b-col>
        <b-col cols="3">
          <b-form-select
            v-model="selectedState"
            :options="stateList"
          ></b-form-select>
        </b-col>
      </b-row>
    </b-form>
  </div>
</template>

<script>
export default {
  props: {
    countries: Array,
  },
  watch: {
    countries(countries) {
      this.countriesList = [{ value: null, text: "Please select an option" }];
      countries.forEach((country) =>
        this.countriesList.push({
          value: country.country_code,
          text: country.country_name,
        })
      );
    },
    selectedCountry(country_code) {
      this.filterBy({by: 'country_code', value: country_code});
    },
    selectedState(state) {
      this.filterBy({by: 'state', value: state});
    },
  },
  data() {
    return {
      selectedCountry: null,
      selectedState: null,
      countriesList: [{ value: null, text: "Please select an option" }],
      stateList: [
        { value: null, text: "Please select an option" },
        { value: 'OK', text: "Valid phone numbers" },
        { value: 'NOK', text: "Invalid phone numbers" },
      ],
    };
  },
  methods: {
    filterBy($filter) {
      this.$root.$emit('filterBy', $filter);
    }
  }
};
</script>