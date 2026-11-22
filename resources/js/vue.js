// customer site: Vue 3 SPA mounted on #root (resources/views/guest/home.blade.php)
import "../css/site.css";

import axios from "axios";
import { createApp } from "vue";
import App from "./App.vue";
import { router } from "./routes.js";
import { createAppStore } from "./store.js";

// same axios instance imported by resources/js/api: every request is marked as AJAX
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

createApp(App)
    .use(createAppStore())
    .use(router)
    .mount("#root");
