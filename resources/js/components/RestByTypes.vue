<template>
    <div class="cont">
        <article
            v-show="selectedTypesLenght"
            class="my-4"
            v-for="(rest, i) in pageOfItems"
            :key="`restaurant_index${i}`"
        >
            <h2 class="my-2">{{ rest.name }}</h2>
            <img class="my-2" :src="rest.img" :alt="rest.name" />
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
                    Mostra
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
    created() {},
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

article {
    background: white;
    padding: 20px;
    box-shadow: 0 0.3px 0.5px rgba(0, 0, 0, 0.022),
        0 0.9px 1.3px rgba(0, 0, 0, 0.031), 0 1.8px 2.7px rgba(0, 0, 0, 0.039),
        0 3.7px 5.5px rgba(0, 0, 0, 0.048), 0 10px 15px rgba(0, 0, 0, 0.07);
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

    .btn.btn-success.btn-small {
        background-color: $brand;
        font-weight: 700;
    }
}
</style>
