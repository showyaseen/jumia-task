<template>
  <div>
    <b-row class="mt-4">
      <b-col>
        <b-table striped hover :items="customers"></b-table>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  props: {
    customers: Array,
  },
};
</script>