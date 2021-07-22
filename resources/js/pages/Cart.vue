<template>
    <div>
        <div class="cont">
            <ul>
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
                        <p>
                            <strong>description: </strong
                            >{{ plate.description }}
                        </p>
                        <p>
                            <strong>ingredients: </strong
                            >{{ plate.ingredients }}
                        </p>
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
            <div class="mt-5 priceBtn-box">
                <h2>
                    <strong class="mr-3">TOTAL PRICE: </strong
                    ><em> {{ total.toFixed(2) }} € </em>
                </h2>
                <router-link
                    v-show="cart.length"
                    :to="{ name: 'payment' }"
                    class="btn btn-success btn-lg ml-5"
                >
                    pay! you whore
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { mapMutations, mapState } from "vuex";
export default {
    name: "Cart",
    components: {},
    computed: {
        ...mapState(["cart", "total"])
    },
    data() {
        return {
            cartNoDuplicates: []
        };
    },
    watch: {
        cart() {
            this.$store.commit("changeTotal");
        }
    },
    created() {
        this.sum();
        this.cartRemoveDuplicate();
    },
    methods: {
        ...mapMutations(["removePlate", "changeTotal"]),
        //
        sum() {
            this.$store.commit("changeTotal");
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

h2 {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    strong,
    em {
        display: inline-block;
    }
}

.priceBtn-box {
    display: flex;
    .btn-lg {
        color: white;
        background-color: $col2;
        transition: transform 0.3s;
        &:hover {
            background-color: $col2;
            transform: scale(1.02);
        }
    }
}
</style>
