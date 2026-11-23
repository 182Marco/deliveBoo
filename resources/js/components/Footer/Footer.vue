<template>
    <footer class="footer">
        <div class="ui-container footer-inner">
            <div class="footer-brand">
                <Logo inverse />
                <p class="footer-tagline">{{ t("footer.tagline") }}</p>
            </div>

            <nav class="footer-cols" :aria-label="t('nav.footer')">
                <div v-for="column in columns" :key="column.title" class="footer-col">
                    <h2 class="footer-col-title">{{ t(column.title) }}</h2>
                    <ul class="footer-list">
                        <li v-for="link in column.links" :key="link.text">
                            <router-link v-if="link.to" :to="link.to" class="footer-link">{{ t(link.text) }}</router-link>
                            <a v-else :href="link.href" class="footer-link">{{ t(link.text) }}</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="ui-container">
            <div class="footer-bottom">
                <p>&copy; {{ year }} Foodsy. {{ t("footer.rights") }}</p>
                <p class="footer-secure">
                    <Icon name="lock" :size="16" />
                    {{ t("footer.secure") }}
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
