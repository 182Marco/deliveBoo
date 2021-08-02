<template>
    <section class="navigation">
        <button @click="$store.dispatch('getRestaurants', 1)">first</button>
        <button
            :disabled="restPagination.current == 1"
            @click="$store.dispatch('getRestaurants', --restPagination.current)"
        >
            prev
        </button>
        <button
            @click="$store.dispatch('getRestaurants', n)"
            v-for="n in restPagination.last"
            :key="`number_${n}`"
            :class="{ active: n == restPagination.current }"
        >
            {{ n }}
        </button>
        <button
            :disabled="restPagination.current == restPagination.last"
            @click="$store.dispatch('getRestaurants', ++restPagination.current)"
        >
            next
        </button>
        <button @click="$store.dispatch('getRestaurants', restPagination.last)">
            last
        </button>
    </section>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "RestPaginateEl",
    computed: {
        ...mapState(["restPagination"])
    }
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";
@import "../../sass/animations";
@import "../../sass/paginationEl";
</style>
