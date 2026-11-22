<template>
    <!-- every click in here bubbles to Hamburger, which closes the menu (also the "x") -->
    <section v-if="menuMobile">
        <header>
            <img src="/../../images/logoColBrand.png" alt="delivero Logo" />
            <p class="x">x</p>
        </header>
        <div class="btn-box">
            <a class="main-btn" href="/login">Registrati o accedi</a>
        </div>
        <main>
            <ul>
                <li v-for="link in mainLinks" :key="link.text">
                    <div>
                        <a :href="link.href"><i :class="link.icon"></i>{{ link.text }}</a>
                    </div>
                    <p>&gt;</p>
                </li>
            </ul>
            <ul class="lang-list">
                <li v-for="lang in languages" :key="lang">
                    <div>
                        <a href="#">{{ lang }}</a>
                    </div>
                    <p>&gt;</p>
                </li>
            </ul>
        </main>
    </section>
</template>

<script>
import { menuMobileLogic } from "./MenuMobile.logic.js";

export default menuMobileLogic;
</script>

<style lang="scss" scoped src="./MenuMobile.scss"></style>
