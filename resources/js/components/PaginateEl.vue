<template>
    <section class="navigation">
        <button :disabled="paginate.current == 1" @click="first">
            first
        </button>
        <button :disabled="paginate.current == 1" @click="prev">
            prev
        </button>
        <button
            class="number"
            @click="getPageByNum(n)"
            v-for="n in paginate.last"
            :key="`number_${n}`"
            :class="{ active: n == paginate.current }"
        >
            {{ n }}
        </button>
        <button :disabled="paginate.current == paginate.last" @click="next">
            next
        </button>
        <button :disabled="paginate.current == paginate.last" @click="last">
            last
        </button>
    </section>
</template>

<script>
import { mapActions } from "vuex";
export default {
    name: "PaginateEl",
    props: {
        getMenu: Boolean,
        paginate: Object,
        slug: String
    },
    methods: {
        // THE COMPONENT SERVES 2 PAGINATIONS USEING THOSE 2 DIFFERNT API
        ...mapActions(["getMenuAndDetails", "getRestaurants"]),
        // ->if component is  used to call api which gets menu of single restaurant
        // ->else component is  used to call api which recives types and returns resturants
        first() {
            if (this.getMenu) {
                this.$store.dispatch("getMenuAndDetails", {
                    page: 1,
                    slug: this.slug
                });
            } else {
                this.$store.dispatch("getRestaurants", 1);
            }
        },
        prev() {
            //  if component used to call api which gets menu
            if (this.getMenu) {
                this.$store.dispatch("getMenuAndDetails", {
                    page: --this.paginate.current,
                    slug: this.slug
                });
            } else {
                this.$store.dispatch("getRestaurants", --this.paginate.current);
            }
        },
        next() {
            if (this.getMenu) {
                this.$store.dispatch("getMenuAndDetails", {
                    page: ++this.paginate.current,
                    slug: this.slug
                });
            } else {
                this.$store.dispatch("getRestaurants", ++this.paginate.current);
            }
        },
        last() {
            if (this.getMenu) {
                this.$store.dispatch("getMenuAndDetails", {
                    page: this.paginate.last,
                    slug: this.slug
                });
            } else {
                this.$store.dispatch("getRestaurants", this.paginate.last);
            }
        },
        getPageByNum(n) {
            if (this.getMenu) {
                this.$store.dispatch("getMenuAndDetails", {
                    page: n,
                    slug: this.slug
                });
            } else {
                this.$store.dispatch("getRestaurants", n);
            }
        }
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
