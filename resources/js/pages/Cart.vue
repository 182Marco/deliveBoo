<template>
    <div>
        <div class="cont">
            <article
                v-for="(plate, i) in cartSingleEl"
                :key="`index_in_cart${i}`"
            >
                <div class="img-box">
                    <img :src="plate.img" :alt="plate.name" />
                </div>
                <div class="txt-box ml-3">
                    <h3>{{ plate.name }}</h3>
                    <div class="portions-box">
                        <button
                            @click="removeFromCart(plate)"
                            class="btn btn-success btn-small mr-3"
                        >
                            <strong>-</strong>
                        </button>
                        <p>
                            <strong>portions: </strong>
                            {{ sameInCartlength(plate) }}
                            <strong>
                                for:
                            </strong>

                            {{
                                (sameInCartlength(plate) * plate.price).toFixed(
                                    2
                                )
                            }}
                            €
                        </p>
                    </div>
                    <p>
                        <strong>single item price: </strong
                        >{{ plate.price.toFixed(2) }} €
                    </p>
                </div>
            </article>

            <div class="mt-5 priceBtn-box">
                <h2>
                    <strong>TOTAL PRICE: </strong
                    ><em> {{ total.toFixed(2) }} € </em>
                </h2>
                <router-link
                    v-show="cart.length"
                    :to="{ name: 'payment' }"
                    class="btn btn-success btn-lg"
                >
                    checkout
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
            cartSingleEl: []
        };
    },
    watch: {
        cart() {
            this.$store.commit("changeTotal");
            this.cartNoDouble();
        }
    },
    created() {
        this.cartNoDouble();
        this.sum();
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
        cartNoDouble() {
            this.cartSingleEl = this.cart.reduce((acc, current) => {
                const x = acc.find(item => item.id === current.id);
                if (!x) {
                    return acc.concat([current]);
                } else {
                    return acc;
                }
            }, []);
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

.cont {
    margin-top: 80px;
    margin-bottom: 80px;
}

ul {
    li {
        display: block;
        margin: 30px 0;
    }
}
article {
    box-shadow: 3px 4px 6px 4px #49494918;
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 27px;
    border-radius: 5px;
    h2,
    h3 {
        font-weight: 700;
    }
    p,
    h3 {
        margin: 8px 0;
    }
}

h2 {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    @include media-desk-first(tablet) {
        font-size: 1.6rem;
        margin-right: 15px;
        margin-bottom: 13px;
    }
    strong,
    em {
        display: inline-block;
        margin-right: 1rem;
    }
}

.priceBtn-box {
    display: flex;
    @include media-desk-first(tablet) {
        flex-direction: column;
        align-items: center;
    }
    .btn-lg {
        color: white;
        background-color: $col2;
        transition: transform 0.3s, background-color 0.3s;
        font-weight: 700;
        border: none;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: 3rem;
        @include media-desk-first(tablet) {
            margin-left: 0;
            width: 50%;
        }
        &:hover {
            background-color: $brand;
            transform: scale(1.03);
            animation: tremble 0.4s;
        }
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

.portions-box {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    button.btn.btn-success.btn-small {
        border: none;
        display: inline-flex;
        height: 30px;
        width: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        border: none;
        background-color: $brand;
        font-weight: 700;
        transition: background-color 0.3s;
        &:hover {
            background-color: $col2;
        }
        strong {
            font-size: 1.5rem;
            color: #fff;
        }
    }
    p {
        display: inline-block;
        font-size: 1.1rem;
    }
}

.img-box {
    width: 200px;
    height: 170px;
    display: inline-flex;
    justify-content: center;
    border-radius: 10px;
    overflow: hidden;
    align-items: center;
    width: 300px;
    @include media-desk-first(tablet) {
        width: 200px;
    }
    img {
        width: 100%;
        object-fit: cover;
    }
}
</style>
