// jQuery + Bootstrap 4 plugins (dropdowns, collapse...) and axios, shared by the admin (app.js) and the SPA (vue.js)
import Popper from "popper.js";
import $ from "jquery";
import "bootstrap"; // the UMD build imports jquery and popper.js by itself
import axios from "axios";

// same axios instance imported by resources/js/api: every request is marked as AJAX
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Popper = Popper;
window.$ = window.jQuery = $;
window.axios = axios;
