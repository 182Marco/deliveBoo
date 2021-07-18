<template>
    <div>
        <article
            v-show="selectedTypesLenght"
            class="my-4"
            v-for="(rest, i) in restByTypes"
            :key="`restaurant_index${i}`"
        >
            <h2 class="my-2">{{ rest.name }}</h2>
            <img class="my-2" :src="rest.img" :alt="rest.name" />
            <br />
            <p class="my-2 d-inline-block">
                {{ rest.city }}, {{ rest.address }}
            </p>
            <router-link
                class="btn btn-success btn-small ml-3"
                :to="{ name: 'restMenu' }"
            >
                menu and details</router-link
            >
        </article>
    </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";

export default {
    name: "RestByTypes",
    props: {},
    data() {
        return {};
    },
    created() {},
    computed: {
        ...mapState(["restByTypes"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {}
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

a {
    color: white;
}
</style>
