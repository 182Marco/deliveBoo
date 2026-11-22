<template>
    <footer class="footer">
        <div class="ui-container footer-inner">
            <div class="footer-brand">
                <Logo inverse />
                <p class="footer-tagline">Good food from the kitchens you love, delivered warm to your door.</p>
            </div>

            <nav class="footer-cols" aria-label="Footer">
                <div v-for="column in columns" :key="column.title" class="footer-col">
                    <h2 class="footer-col-title">{{ column.title }}</h2>
                    <ul class="footer-list">
                        <li v-for="link in column.links" :key="link.text">
                            <router-link v-if="link.to" :to="link.to" class="footer-link">{{ link.text }}</router-link>
                            <a v-else :href="link.href" class="footer-link">{{ link.text }}</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="ui-container">
            <div class="footer-bottom">
                <p>&copy; {{ year }} Foodsy. All rights reserved.</p>
                <p class="footer-secure">
                    <Icon name="lock" :size="16" />
                    Card payments are processed securely.
                </p>
            </div>
        </div>
    </footer>
</template>

<script>
import { footerLogic } from "./Footer.logic.js";

export default footerLogic;
</script>

<style scoped src="./Footer.css"></style>
