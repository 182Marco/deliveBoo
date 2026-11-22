<template>
    <div class="app">
        <Header />
        <!-- nothing inside <router-view>: in Vue Router 4 any content becomes its slot -->
        <router-view />
        <Content class="main-content" />
        <Footer class="footer" />
    </div>
</template>

<script>
import { appLogic } from "./App.logic.js";

export default appLogic;
</script>

<style lang="scss" src="./App.scss"></style>
