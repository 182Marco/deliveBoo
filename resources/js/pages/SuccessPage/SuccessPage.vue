<template>
    <div class="cont">
        <div class="wrap-thanks">
            <div class="thanks">
                <h1>Thank you!</h1>
                <p>Thanks for ordering from us!</p>
                <p>You should receive a confirmation email soon!</p>
                <router-link :to="{ name: 'home' }" class="home">go to Home</router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { successPageLogic } from "./SuccessPage.logic.js";

export default successPageLogic;
</script>

<style lang="scss" scoped src="./SuccessPage.scss"></style>
