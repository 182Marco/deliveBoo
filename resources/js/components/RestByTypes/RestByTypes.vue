<template>
    <div>
        <article
            v-for="(rest, i) in restByTypes"
            v-show="selectedTypesLenght"
            :key="`restaurant_index${i}`"
            class="article card"
        >
            <router-link class="img-box" :to="menuRoute(rest)">
                <img :src="rest.img" :alt="rest.name" />
            </router-link>
            <div class="txt-box">
                <h2>{{ rest.name }}</h2>
                <p class="d-inline-block txt">
                    <strong>address: </strong> {{ rest.city }},
                    {{ rest.address }}
                </p>
                <router-link class="btn btn-success btn-small txt" :to="menuRoute(rest)">
                    show
                </router-link>
            </div>
        </article>
        <PaginateEl :paginate="restPagination" />
    </div>
</template>

<script>
import { restByTypesLogic } from "./RestByTypes.logic.js";

export default restByTypesLogic;
</script>

<style lang="scss" scoped src="./RestByTypes.scss"></style>
