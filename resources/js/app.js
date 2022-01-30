require('./bootstrap');

window.Vue = require('vue').default;
import Vue from 'vue'

import App from './App.vue';
import VueAxios from 'vue-axios';
import axios from 'axios';

import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue)

Vue.config.productionTip = false

const app = new Vue({
    el: '#app',
    render: h => h(App),
});

