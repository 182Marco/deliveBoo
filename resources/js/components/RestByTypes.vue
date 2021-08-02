<template>
    <div>
        <article
            v-show="selectedTypesLenght"
            class="article card"
            v-for="(rest, i) in restByTypes"
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
        <RestPaginateEl />
    </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import RestPaginateEl from "./RestPaginateEl.vue";

export default {
    name: "RestByTypes",
    components: {
        RestPaginateEl
    },
    computed: {
        ...mapState(["restByTypes"]),
        ...mapGetters(["selectedTypesLenght"])
    }
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";
@import "../../sass/animations";

article.article.card {
    background-color: $foft-back;
    border-radius: 10px;
    display: flex;
    flex-direction: row;
    align-items: center;
    border: none;
    margin-bottom: 20px;
    padding: 10px 0;
    margin-bottom: 30px;
    box-shadow: 15px 15px 10px -10px rgba($color: #000, $alpha: 0.2);
    border-radius: 5px;
    @include media-desk-first(xl-tablet) {
        flex-direction: column;
    }
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
</style>
