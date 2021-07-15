/*
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require("./bootstrap");

window.Vue = require("vue");
// I register also axios as prototipe property of obj window
window.axios = require("axios");

// init vue main instance
import App from "./App.vue";
// import the router
import router from "./routes";

const root = new Vue({
    el: "#root",
    // keyword already register in Vue.js (router)-> assigned with var router
    //  (match which is possible thanks to "export default" at last line of routes file)
    // LONG SINTAX
    // router: router,
    // SHORT SINTAX WITH ES6 -> obj prop name = to his value -> write it just once
    router,
    render: h => h(App)
});
