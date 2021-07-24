<template>
    <div class="cont">
        <article
            v-show="selectedTypesLenght"
            class="my-4 article card"
            v-for="(rest, i) in pageOfItems"
            :key="`restaurant_index${i}`"
        >
            <h2 class="my-2">{{ rest.name }}</h2>
            <!-- Add Cover Image -->
            <div class="img-box">
                <img :src="rest.img" :alt="rest.name" class="ml-4" />
            </div>

            <br />
            <p class="my-2 d-inline-block">
                <strong>adress: </strong> {{ rest.city }}, {{ rest.address }}
            </p>
            <div>
                <router-link
                    v-show="restByTypes"
                    class="btn btn-success btn-small ml-3"
                    :to="{ name: 'restMenu', params: { id: rest.id } }"
                >
                    show
                </router-link>
            </div>
        </article>
        <!-- pagination element -->
        <div class="card-footer pb-0 pt-3">
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
    created() {
        console.warn(
            `This the http for the fist img:  ${this.restByTypes[0].img}`
        );
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

article.article.card {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding: 15px 35px;
    margin-bottom: 30px;
    box-shadow: 3px 4px 6px 4px #5acdbe18;
    border-radius: 5px;
    a {
        color: white;
    }

    h2 {
        font-weight: 700;
    }

    div {
        margin-top: 20px;
        margin-left: -20px;
    }
}

.img-box {
    // border: 1px solid red;
    width: 400px;
    height: 200px;
    display: inline-flex;
    width: 300px;
    @include media-desk-first(tablet) {
        width: 200px;
    }
    img {
        width: 100%;
    }
}

.btn.btn-success.btn-small {
    background-color: $brand;
    font-weight: 700;
    border: none;
    transition: background-color 0.4s;
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
