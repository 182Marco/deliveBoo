<template>
    <!-- render only once the chosen type is known -->
    <div v-if="currentType" class="cont">
        <h1>
            Here only
            <span>{{ currentType.name }}{{ titleSuffix }}</span>
        </h1>
        <RestByTypes />
        <div class="btn-box">
            <router-link :to="{ name: 'home' }" class="btn btn-success btn-small txt" @click="scrollToTypes">
                Select more types
            </router-link>
        </div>
    </div>
</template>

<script>
import { singleTypeRestLogic } from "./SingleTypeRest.logic.js";

export default singleTypeRestLogic;
</script>

<style lang="scss" scoped src="./SingleTypeRest.scss"></style>
