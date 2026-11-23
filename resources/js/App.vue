<template>
    <div class="app">
        <a class="app-skip" href="#main">{{ t("skip") }}</a>
        <Header />
        <!-- nothing inside <router-view>: in Vue Router 4 any content becomes its slot -->
        <main id="main" class="app-main" tabindex="-1">
            <router-view />
        </main>
        <Footer />
    </div>
</template>

<script>
import { appLogic } from "./App.logic.js";

export default appLogic;
</script>

<style src="./App.css"></style>
