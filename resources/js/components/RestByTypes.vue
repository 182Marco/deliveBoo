<template>
    <div class="cont">
        <article
            v-show="selectedTypesLenght"
            class="article card"
            v-for="(rest, i) in pageOfItems"
            :key="`restaurant_index${i}`"
        >
            <router-link
                class="img-box"
                :to="{ name: 'restMenu', params: { id: rest.id } }"
            >
                <img :src="rest.img" :alt="rest.name" />
            </router-link>
            <div class="txt-box">
                <h2>{{ rest.name }}</h2>
                <!-- Add Cover Image -->
                <p class="d-inline-block txt">
                    <strong>address: </strong> {{ rest.city }},
                    {{ rest.address }}
                </p>
                <router-link
                    v-show="restByTypes"
                    class="btn btn-success btn-small txt"
                    :to="{ name: 'restMenu', params: { id: rest.id } }"
                >
                    show
                </router-link>
            </div>
        </article>
        <!-- pagination element -->
        <div class="card-footer pagination tool pb-0 pt-3">
            <jw-pagination
                :pageSize="4"
                :items="restByTypes"
                @changePage="onChangePage"
            ></jw-pagination>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";

export default {
    name: "RestByTypes",
    props: {},
    data() {
        return {
            // pagination data
            pageOfItems: []
        };
    },
    computed: {
        ...mapState(["restByTypes"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {
        // pagination method
        onChangePage(pageOfItems) {
            // update page of items
            this.pageOfItems = pageOfItems;
        }
    }
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

.cont {
    @include media-desk-first(l-tablet) {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
    }
}

article.article.card {
    @include media-desk-first(l-tablet) {
        margin-right: 15px;
        width: calc(100% / 2 - 15px);
    }
    @include media-desk-first(s-tablet) {
        margin-right: 0;
        width: 100%;
        align-items: flex-start;
        padding-bottom: 25px;
    }
    background-color: $foft-back;
    border-radius: 10px;
    display: flex;
    flex-direction: row;
    align-items: center;
    border: none;
    @include media-desk-first(l-tablet) {
        flex-direction: column;
    }
    margin-bottom: 20px;
    padding: 10px 0;
    margin-bottom: 30px;
    box-shadow: 15px 15px 10px -10px rgba($color: #000, $alpha: 0.2);
    border-radius: 5px;
    a {
        color: white;
    }

    h2 {
        font-weight: 700;
        @include media-desk-first(l-tablet) {
            margin-top: 10px;
        }
    }
}

.img-box {
    margin-left: 10px;
    width: 400px;
    height: 200px;
    display: inline-flex;
    width: 300px;
    @include media-desk-first(l-tablet) {
        width: 80%;
        height: 60%;
        margin-left: 0;
    }
    @include media-desk-first(s-tablet) {
        width: 96%;
        height: 70%;
        margin-left: 10px;
    }
    img {
        width: 100%;
        border-radius: 5px;
    }
}

.txt-box {
    position: relative;
    padding: 7px;
    margin-left: 60px;
    padding-right: 10px;
    @include media-desk-first(l-tablet) {
        margin-left: 10px;
    }
    @include media-desk-first(s-tablet) {
        width: 100%;
    }
    .txt {
        padding-right: 15px;
        margin: 10px 0;
        @include media-desk-first(s-tablet) {
            margin-top: 0px;
        }
    }
}

.btn.btn-success.btn-small {
    background-color: $brand;
    font-weight: 700;
    border: none;
    transition: background-color 0.4s;
    margin-bottom: 21px;
    @include media-desk-first(s-phone) {
        position: absolute;
        top: 80%;
        right: 20%;
    }
    &:hover {
        animation: tremble 0.4s;
        background-color: $col2;
    }
}

@keyframes tremble {
    0% {
        transform: rotate(0deg);
    }
    20% {
        transform: rotate(3deg);
    }
    40% {
        transform: rotate(-3deg);
    }
    60% {
        transform: rotate(3deg);
    }
    80% {
        transform: rotate(-3deg);
    }
    100% {
        transform: rotate(0deg);
    }
}
</style>
