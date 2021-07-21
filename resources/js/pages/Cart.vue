<template>
    <div>
        <ul class="cont">
            <li
                v-for="(plate, i) in cartNoDuplicates"
                :key="`index_in_cart${i}`"
            >
                <article v-show="sameInCartlength(plate)">
                    <h2>{{ plate.name }}</h2>
                    <h3>
                        <strong
                            >portions:
                            {{ sameInCartlength(plate) }}
                        </strong>
                    </h3>
                    <img :src="plate.img" :alt="plate.name" />
                    <p><strong>description: </strong>{{ plate.description }}</p>
                    <p><strong>ingredients: </strong>{{ plate.ingredients }}</p>
                    <p><strong>price: </strong>{{ plate.price }} €</p>
                    <button
                        @click="removeFromCart(plate)"
                        class="btn btn-success btn-small mr-3"
                    >
                        Remove a portion from cart
                    </button>
                </article>
            </li>
        </ul>
        <h2><strong>TOTAL PRICE: </strong>{{ total.toFixed(2) }} €</h2>
        <router-link
            :to="{ name: 'payment' }"
            class="btn btn-success btn-lg mt-4"
        >
            pay! you whore
        </router-link>
    </div>
</template>

<script>
import { mapMutations, mapState } from "vuex";
export default {
    name: "Cart",
    components: {},
    computed: {
        ...mapState(["cart"])
    },
    data() {
        return {
            total: 0,
            cartNoDuplicates: []
        };
    },
    watch: {
        cart() {
            let sum = 0;
            this.cart.forEach(e => (sum += e.price));
            this.total = sum;
        }
    },
    created() {
        this.sum();
        this.cartRemoveDuplicate();
    },
    methods: {
        ...mapMutations(["removePlate"]),
        sum() {
            this.cart.forEach(e => (this.total += e.price));
        },
        removeFromCart(plateObj) {
            this.$store.commit("removePlate", plateObj);
        },
        cartRemoveDuplicate() {
            this.cartNoDuplicates = this.cart.filter(
                (e, i, a) => a.indexOf(e) === i
            );
        },
        // array of same element in cart.length
        sameInCartlength(plate) {
            return this.cart.filter(e => e.id == plate.id).length;
        }
    }
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

article {
    margin: 30px 0;
    h2 {
        font-weight: 700;
    }
    p,
    h3 {
        margin: 10px 0;
    }
    .btn.btn-success.btn-small {
        margin-top: 10px;
        background-color: $brand;
        font-weight: 700;
    }
}
.btn-lg.mt-4 {
    color: white;
    background-color: $brand;
}
</style>
