import { createRouter, createWebHistory } from "vue-router";

import Home from "./pages/Home.vue";
import RestMenu from "./pages/RestMenu.vue";
import Cart from "./pages/Cart.vue";
import Payment from "./pages/Payment.vue";
import SuccessPage from "./pages/SuccessPage.vue";
import NotFound from "./pages/NotFound.vue";
import singleTypeRest from "./pages/singleTypeRest.vue";

const router = createRouter({
    history: createWebHistory(),
    linkExactActiveClass: "active",
    routes: [
        {
            path: "/",
            name: "home",
            component: Home,
            props: true
        },
        {
            path: `/chosen-restaurant-menu-and-details/:slug`,
            name: "restMenu",
            component: RestMenu
        },
        {
            path: `/Restaurants-matching-the-single-type-you-choose/:id`,
            name: "singleType",
            component: singleTypeRest
        },
        {
            path: `/your-cart`,
            name: "cart",
            component: Cart
        },
        {
            path: "/payment",
            name: "payment",
            component: Payment
        },
        {
            path:
                "/well-done!Successfull-payment!Your-order-has-been-confirmed",
            name: "success",
            component: SuccessPage
        },
        {
            // catch-all (Vue Router 4 syntax for the old "*")
            path: "/:pathMatch(.*)*",
            component: NotFound
        }
    ]
});

export default router;
