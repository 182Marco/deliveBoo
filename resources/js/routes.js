import { createRouter, createWebHistory } from "vue-router";
import { Cart, Home, NotFound, Payment, RestMenu, SingleTypeRest, SuccessPage } from "./pages";

const routes = [
    { path: "/", name: "home", component: Home },
    { path: "/chosen-restaurant-menu-and-details/:slug", name: "restMenu", component: RestMenu },
    { path: "/Restaurants-matching-the-single-type-you-choose/:id", name: "singleType", component: SingleTypeRest },
    { path: "/your-cart", name: "cart", component: Cart },
    { path: "/payment", name: "payment", component: Payment },
    {
        path: "/well-done!Successfull-payment!Your-order-has-been-confirmed",
        name: "success",
        component: SuccessPage
    },
    // catch-all (Vue Router 4 syntax for the old "*")
    { path: "/:pathMatch(.*)*", component: NotFound }
];

const router = createRouter({
    history: createWebHistory(),
    linkExactActiveClass: "active",
    routes
});

export { router };
