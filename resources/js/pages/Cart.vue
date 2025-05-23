<template>
    <div>
        <div class="cont">
            <button
                v-show="cart.length"
                :to="{ name: 'home' }"
                class="btn btn-danger btn-sm home"
                @click="empty"
            >
                empty Cart
            </button>
            <br />
            <button
                class="btn btn-warning btn-sm text-white my-3"
                @click="$router.go(-1)"
            >
                previous restaurant
            </button>
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
                            class="btn btn-success btn-small mr-3 no focus"
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
                        <button
                            @click="otherPortionInCart(plate)"
                            class="btn btn-success btn-small ml-2 no focus"
                        >
                            <strong>+</strong>
                        </button>
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
                    class="btn btn-success btn-lg uge"
                >
                    checkout
                </router-link>
                <router-link
                    v-show="!cart.length"
                    :to="{ name: 'home' }"
                    class="btn btn-success btn-lg uge"
                >
                    home
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
        ...mapMutations([
            "removePlate",
            "addPlate",
            "changeTotal",
            "emptyCart"
        ]),
        //
        sum() {
            this.$store.commit("changeTotal");
        },
        removeFromCart(plateObj) {
            this.$store.commit("removePlate", plateObj);
        },
        otherPortionInCart(plateObj) {
            this.$store.commit("addPlate", plateObj);
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
        },
        empty() {
            this.$store.commit("emptyCart");
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
    background: #fbfbfb;
    box-shadow: 3px 4px 6px 4px #3f3f3f18;
    display: flex;
    align-items: center;
    padding: 10px;
    margin: 27px 0;
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
        // fixed width so it won't push button checkout on the right
        width: 175px;
    }
    // fixed width so it won't push button checkout on the right
    strong {
        width: 215px;
    }
}

.priceBtn-box {
    display: flex;
    @include media-desk-first(tablet) {
        flex-direction: column;
        align-items: center;
    }
    .btn-lg {
        border: none;
        color: white;
        background-color: $col2;
        transition: transform 0.3s, background-color 0.3s;
        font-weight: 700;
        border: none;
        display: flex;
        justify-content: center;
        align-items: center;
        &.uge {
            padding: 5px 30px;
            font-size: 2rem;
            border-radius: 10px;
        }
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
// small btns to remove
// lots of classes take out bootstrap focus on btn
.portions-box {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    button.btn.btn-success.btn-small.no.focus {
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
        box-shadow: none;
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
        // fixed width so it won't push button add on the right
        width: 215px;
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
