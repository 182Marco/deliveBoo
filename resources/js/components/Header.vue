<template>
    <header>
        <div class="cont">
            <nav>
                <img src="../img/logo.png" alt="logo" />
                <ul>
                    <li class="desktop">
                        <router-link :to="{ name: 'home' }">Home</router-link>
                    </li>
                    <li class="desktop">
                        <a href="http://127.0.0.1:8000/register"
                            >Register o access</a
                        >
                    </li>
                    <li>
                        <router-link
                            :to="{ name: 'cart' }"
                            class="cart"
                            @click.native="windowScroll"
                            ><i class="fas fa-shopping-cart">
                                <div class="cart-length">
                                    {{ $store.getters.cartLenght }}
                                </div></i
                            ></router-link
                        >
                    </li>
                    <li>
                        <Hamburger />
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</template>

<script>
import { mapGetters } from "vuex";
import Hamburger from "./Hamburger.vue";

export default {
    name: "Header",
    components: {
        Hamburger
    },
    computed: {
        ...mapGetters(["cartLenght"])
    },
    methods: {
        windowScroll() {
            window.scrollTo(0, 0);
        }
    }
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

header {
    background-color: $brand;
    padding: 30px 20px;
    margin-bottom: 80px;
    nav {
        @include flex(row, space-between, center);
        img {
            width: 140px;
        }
        ul {
            li {
                margin-left: 20px;
                &:last-child {
                    margin-left: 0;
                    display: none;
                    @include media-desk-first(tablet) {
                        display: inline-block;
                    }
                }
                a {
                    display: inline-block;
                    font-weight: 700;
                    color: $white;
                    transition: all 0.25s;
                    &:hover {
                        text-decoration: none;
                        transform: scale(1.05);
                        color: $col2;
                    }
                    &.cart {
                        position: fixed;
                        right: 11.5%;
                        top: 14%;
                        transition: all 0.3s;
                        z-index: 10;
                        &:hover {
                            transform: scale(1.03);
                        }
                    }
                    i {
                        display: inline-block;
                        color: white;
                        font-size: 2.2rem;
                        position: relative;
                        color: $col4;
                        transition: all 0.3s;
                        &:hover {
                            color: $col2;
                        }
                        .cart-length {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            position: absolute;
                            bottom: 75%;
                            left: 75%;
                            height: 28px;
                            width: 28px;
                            background-color: red;
                            color: white;
                            border-radius: 50%;
                            font-size: 1rem;
                            font-weight: 700;
                        }
                    }
                }
            }
        }
    }
}

.desktop {
    @include media-desk-first(tablet) {
        display: none;
    }
}

.hamburger {
    width: 27px;
    margin-right: 0;
    cursor: pointer;
    &:hover .line {
        background-color: $col2;
    }
    .line {
        display: block;
        width: 100%;
        height: 4px;
        background-color: $white;
        border-radius: 5px;
        margin-top: 4px;
        transition: background 0.5s;
    }
}

.active {
    color: $col2;
    i {
        color: $col2;
    }
}
</style>
