// I do not put dots or slashes because
//  if the framework doesn't see anything
// it would look inside the node modules
import Vue from "vue";
import VueRouter from "vue-router";
// ***

// here we import the components for the pages
import Home from "./pages/Home.vue";
import Register from "./pages/Register.vue";
import WorkWithUs from "./pages/WorkWithUs.vue";
import Menu from "./pages/Menu.vue";
import NotFound from "./pages/NotFound.vue";

// through a method vue"use" we tell Vue to work with router
Vue.use(VueRouter);
// ****
// instance of router that will work with Vue.js
const router = new VueRouter({
    // tell the library to emulate a navigation server side ->
    // We will be able to go back in pages We've seen
    mode: "history",
    // shorten the name of the active class brought in from router
    linkExactActiveClass: "active",
    routes: [
        {
            path: "/",
            // what I use to call it in attribute to of router-link tag
            // SYNTAX TO CALL IT ->  :to="{ name: 'home' }"
            name: "home",
            component: Home,
            props: true
        },
        {
            path: "/registrati-o-accedi",
            name: "log",
            component: Register
        },
        {
            path: "/collabora-con-noi",
            name: "collabora",
            component: WorkWithUs
        },
        {
            path: "/menu",
            name: "plates",
            component: Menu
        },
        // to go in component 404 if you write
        // in browser routes that don't exist
        {
            path: "*",
            component: NotFound
        }
    ]
});

// necessary to see this var in the App.vue ->
// in other files in general
export default router;
