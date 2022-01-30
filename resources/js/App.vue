<template>
  <div class="container">
      <b-card
      img-src="https://group.jumia.com/_nuxt/img/j-group.67a6140.svg"
      img-top
      header-tag="header"
      title="Phone Numbers"
      class="jumia_img mt-4"
    >
      <search-fields  :countries="countries" ></search-fields>
      <customer-list :customers="customers"></customer-list>
      <pagination align="center" :data="customersPagination" @pagination-change-page="getCustomerList"></pagination>    
      </b-card>
  </div>
</template>

<script>
import axios from "axios";
import pagination from 'laravel-vue-pagination'
import CustomerList from "./components/CustomerList.vue";
import SearchFields from "./components/SearchFields.vue";
export default {
  components: {
    CustomerList,
    SearchFields,
    pagination
  },
  data() {
    return {
      filters: {},
      customers: [],
      countries: [],
      customersPagination: {}
    };
  },
  created() {
    this.getCustomerList();
    this.getCountriesList();

    this.$root.$on('filterBy', (filterBy) => {
      this.filters[filterBy.by] = filterBy.value;
      this.getCustomerList();
    })
  },
  methods: {
    getCustomerList(page = 1) {
      axios
        .post(`/api/customers?page=${page}`, {filters: this.filters})
        .then((response) => {
          this.customers = response.data.data;
          this.customersPagination = response.data;
        })
        .catch((err) => {
          console.log("some thing wrong happened");
        });
    },
    getCountriesList() {
      axios
        .get(`/api/countries-list`)
        .then((response) => {
          this.countries = response.data.data;
        })
        .catch((err) => {
          console.log("some thing wrong happened");
        });
    }
  }
};
</script>

