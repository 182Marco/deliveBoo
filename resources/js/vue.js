// customer site: Vue 3 SPA mounted on #root (resources/views/guest/home.blade.php)
import "./bootstrap"; // jQuery/Bootstrap plugins and the X-Requested-With header of axios
import "bootstrap/dist/css/bootstrap.css";

import { createApp } from "vue";
import App from "./App.vue";
import { router } from "./routes.js";
import { createAppStore } from "./store.js";

createApp(App)
    .use(createAppStore())
    .use(router)
    .mount("#root");
