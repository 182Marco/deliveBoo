<template>
    <header>
        <div class="cont">
            <nav>
                <img src="../../img/logo.png" alt="logo" />
                <ul>
                    <li class="desktop">
                        <router-link :to="{ name: 'home' }">Home</router-link>
                    </li>
                    <li class="desktop">
                        <a href="/register">Register Partnership</a>
                    </li>
                    <li class="desktop">
                        <a href="/login">Partner Login</a>
                    </li>
                    <li>
                        <router-link :to="{ name: 'cart' }" class="cart" @click="scrollToTop">
                            <i class="fas fa-shopping-cart">
                                <div class="cart-length">
                                    {{ cartLenght }}
                                </div>
                            </i>
                        </router-link>
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
import { headerLogic } from "./Header.logic.js";

export default headerLogic;
</script>

<style lang="scss" scoped src="./Header.scss"></style>
