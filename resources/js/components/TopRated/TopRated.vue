<template>
    <div>
        <h2>Top 12 rated restaurants:</h2>
        <section>
            <div
                v-for="(rest, i) in topRest"
                :key="`rest__id${rest.id}`"
                class="box"
                @click="goToRest(rest.slug)"
            >
                <img class="rest-img" :src="rest.img" :alt="rest.name" />
                <div class="layer"></div>
                <i v-if="medals[i]" class="fas fa-medal" :class="medals[i]"></i>
                <StarsBox :average="rest.average" :top-list="true" />
                <p class="name">{{ rest.name }}</p>
            </div>
        </section>
    </div>
</template>

<script>
import { topRatedLogic } from "./TopRated.logic.js";

export default topRatedLogic;
</script>

<style lang="scss" scoped src="./TopRated.scss"></style>
