<template>
    <article class="plate">
        <h4>{{ plate.name }}</h4>
        <img :src="plate.img" :alt="plate.name" />
        <p><strong>description: </strong> {{ plate.description }}</p>
        <p><strong>ingredients: </strong> {{ plate.ingredients }}</p>
        <p>
            <strong>available: </strong>
            <span v-if="plate.visible"> yes</span>
            <span v-else>no</span>
        </p>
        <p><strong>price: </strong>{{ plate.price }} €</p>
        <div class="btns-box">
            <button
                @click="addToCart(plate)"
                class="btn btn-success btn-small mr-3"
            >
                Add a portion to cart
            </button>
            <button
                v-show="cart.filter(e => e.id == plate.id).length"
                @click="removeFromCart(plate)"
                class="btn btn-success btn-small mr-3"
            >
                Remove a portion from cart
            </button>
            <router-link
                :to="{ name: 'cart' }"
                class="cart-btn btn-success btn btn-small"
            >
                check the cart
            </router-link>
            <p>
                <strong>portions already in cart: </strong>
                {{ cart.filter(e => e.id == plate.id).length }}
            </p>
        </div>
    </article>
</template>

<script>
import { mapGetters, mapMutations, mapState } from "vuex";
export default {
    name: "Plate",
    props: {
        plate: Object
    },
    computed: {
        ...mapState(["cart"]),
        ...mapGetters(["cartLenght"])
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate"]),
        //
        addToCart(plateObj) {
            this.$store.commit("addPlate", plateObj);
        },
        removeFromCart(plateObj) {
            this.$store.commit("removePlate", plateObj);
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

.plate {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding: 10px 0px;
}

.plate:last-child {
    margin-bottom: 20px;
    border-bottom: none;
    padding: 10px 0px;
}

h4 {
    font-weight: 700;
}

p {
    margin: 8px 0;
}

button,
a {
    transition: transform 0.2s;
    &:hover {
        transform: scale(1.01);
    }
}

.btns-box {
    position: relative;
    .btn.btn-success.btn-small {
        margin-top: 10px;
        background-color: $brand;
        border-color: $brand;
        font-weight: 700;
        &.cart-btn {
            color: $white;
            background-color: $col2;
            border-color: $col2;
            position: absolute;
            right: 0;
        }
    }
}
</style>
