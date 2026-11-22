<template>
    <!-- hidden while the warning "one restaurant per order" is open -->
    <footer v-show="!warn" class="footer">
        <div class="cont">
            <div class="card-container">
                <div v-for="card in linkCards" :key="card.title" class="card">
                    <h3>{{ card.title }}</h3>
                    <ul>
                        <li v-for="link in card.links" :key="link">
                            <a href="#">{{ link }}</a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Take Deliveroo with you</h3>
                    <ul>
                        <li v-for="badge in storeBadges" :key="badge.alt">
                            <a href="#" class="store">
                                <div>
                                    <img :src="badge.src" :alt="badge.alt" />
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="icons-copy">
                <ul class="icons">
                    <li v-for="icon in socialIcons" :key="icon">
                        <a href="#"><i :class="icon"></i></a>
                    </li>
                </ul>
                <div class="copyright">&copy; 2021 Deliveroo</div>
            </div>
        </div>
    </footer>
</template>

<script>
import { footerLogic } from "./Footer.logic.js";

export default footerLogic;
</script>

<style lang="scss" scoped src="./Footer.scss"></style>
