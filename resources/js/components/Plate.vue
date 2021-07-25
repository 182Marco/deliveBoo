<template>
    <div>
        <div class="menu-wrap">
            <article class="plate card">
                <h3 class="mb-1">{{ plate.name }}</h3>
                <div class="img-box">
                    <img :src="plate.img" :alt="plate.name" />
                </div>
                <p><strong>Description: </strong> {{ plate.description }}</p>
                <p><strong>Ingredients: </strong> {{ plate.ingredients }}</p>
                <p>
                    <strong>Available: </strong>
                    <span v-if="plate.visible"> yes</span>
                    <span v-else>no</span>
                </p>
                <p><strong>Price: </strong>{{ plate.price }} €</p>
                <div class="btns-box">
                    <button
                        @click="addToCart(plate)"
                        class="btn btn-success btn-small"
                    >
                        + Add a portion to cart
                    </button>
                    <button
                        v-if="cart.filter(e => e.id == plate.id).length"
                        @click="removeFromCart(plate)"
                        class="btn btn-success btn-small"
                    >
                        - Remove a portion from cart
                    </button>
                    <router-link
                        :to="{ name: 'cart' }"
                        class="cart-btn btn-success btn btn-small"
                        @click.native="windowScroll"
                    >
                        check the cart
                    </router-link>
                </div>
                <p>
                    <strong class="item-in-cart"
                        >portions already in cart:
                        {{ cart.filter(e => e.id == plate.id).length }}
                    </strong>
                </p>
            </article>
            <Warning v-if="warn" />
        </div>
    </div>
</template>

<script>
import { mapGetters, mapMutations, mapState } from "vuex";
import Warning from "./Warning.vue";

export default {
    name: "Plate",
    props: {
        plate: Object
    },
    components: {
        Warning
    },
    computed: {
        ...mapState(["cart", "warn"]),
        ...mapGetters(["cartLenght"])
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate"]),
        //
        addToCart(plateObj) {
            // if cart empty or contains just obj from same restaurant
            if (
                !this.cart.length ||
                this.cart.filter(e => e.restaurant_id == plateObj.restaurant_id)
                    .length
            ) {
                this.$store.commit("addPlate", plateObj);
            } else {
                this.$store.commit("changeWarn");
            }
        },
        removeFromCart(plateObj) {
            this.$store.commit("removePlate", plateObj);
        },
        windowScroll() {
            window.scrollTo(0, 0);
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

.menu-wrap {
    background-color: rgb(247, 247, 247);
    padding: 30px;
}

.plate.card {
    margin-bottom: 0px;
    border-bottom: 1px solid #ddd;
    padding: 20px;
}

.card {
    border: none;
    box-shadow: 15px 15px 10px -10px rgba($color: #000, $alpha: 0.3);
}

// .plate:last-child {
//     margin-bottom: 20px;
//     border-bottom: none;
//     padding: 10px 0px;
// }

h3 {
    font-weight: 700;
    font-size: 1.6rem;
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
    display: flex;
    @include media-desk-first(xs-desktop) {
        flex-direction: column;
    }
    .btn.btn-success.btn-small {
        margin-top: 10px;
        margin-right: 10px;
        background-color: $brand;
        border-color: $brand;
        font-weight: 700;
        @include media-desk-first(xs-desktop) {
            margin-right: 0;
        }
        &.cart-btn {
            color: $white;
            background-color: $col2;
            border-color: $col2;
            position: absolute;
            right: 0;
            @include media-desk-first(xs-desktop) {
                position: static;
            }
        }
    }
}

.img-box {
    // border: 1px solid red;
    width: 300px;
    height: 200px;
    display: flex;
    width: 300px;
    @include media-desk-first(tablet) {
        width: 200px;
    }
    img {
        width: 100%;
        object-fit: contain;
    }
}

article p .item-in-cart {
    color: $col2;
}
</style>
