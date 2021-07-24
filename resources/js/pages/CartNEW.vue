<template>
    <div>
        <article
            v-for="(plate, i) in removeCartDuplicates"
            :key="`cart_index:${i}`"
        >
            <h1>{{ plate.name }}</h1>
        </article>
    </div>
</template>

<script>
import { mapMutations, mapState, mapGetters } from "vuex";
export default {
    name: "Cart",
    components: {},
    computed: {
        ...mapState(["cart", "total"]),
        ...mapGetters(["cartRemoveDuplicate"])
    },
    created() {
        this.cartNoDouble();
        this.sum();
    },
    data() {
        return {
            cartSingleEl: []
        };
    },
    watch: {
        cart() {
            this.$store.commit("changeTotal");
        }
    },
    methods: {
        ...mapMutations(["removePlate", "changeTotal"]),
        //
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
        sum() {
            this.$store.commit("changeTotal");
        },
        removeFromCart(plateObj) {
            this.$store.commit("removePlate", plateObj);
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

ul {
    li {
        display: block;
        margin: 30px 0;
    }
}
article {
    padding: 10px;
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
        transition: transform 0.3s, background-color 0.3s;
        font-weight: 700;
        border: none;
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
    button.btn.btn-success.btn-small.mr-1 {
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
</style>
