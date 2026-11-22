// customer site: Vue 3 SPA mounted on #root (resources/views/guest/home.blade.php)
import "./bootstrap"; // also sets window.axios, used by the components
import "bootstrap/dist/css/bootstrap.css";

import { createApp } from "vue";
import App from "./App.vue";
import router from "./routes";
import store from "./store.js";

createApp(App)
    .use(store())
    .use(router)
    .mount("#root");
