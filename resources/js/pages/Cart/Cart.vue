<template>
    <div>
        <div class="cont">
            <button v-show="cart.length" class="btn btn-danger btn-sm home" @click="emptyCart">
                empty Cart
            </button>
            <br />
            <button class="btn btn-warning btn-sm text-white my-3" @click="$router.go(-1)">
                previous restaurant
            </button>
            <article v-for="(plate, i) in plates" :key="`index_in_cart${i}`">
                <div class="img-box">
                    <img :src="plate.img" :alt="plate.name" />
                </div>
                <div class="txt-box ml-3">
                    <h3>{{ plate.name }}</h3>
                    <div class="portions-box">
                        <button class="btn btn-success btn-small mr-3 no focus" @click="removePlate(plate)">
                            <strong>-</strong>
                        </button>
                        <p>
                            <strong>portions: </strong>
                            {{ portionsOf(plate) }}
                            <strong> for: </strong>
                            {{ formatPrice(portionsOf(plate) * plate.price) }}
                            €
                        </p>
                        <button class="btn btn-success btn-small ml-2 no focus" @click="addPlate(plate)">
                            <strong>+</strong>
                        </button>
                    </div>
                    <p>
                        <strong>single item price: </strong>{{ formatPrice(plate.price) }} €
                    </p>
                </div>
            </article>

            <div class="mt-5 priceBtn-box">
                <h2>
                    <strong>TOTAL PRICE: </strong><em> {{ formatPrice(total) }} € </em>
                </h2>
                <router-link v-show="cart.length" :to="{ name: 'payment' }" class="btn btn-success btn-lg uge">
                    checkout
                </router-link>
                <router-link v-show="!cart.length" :to="{ name: 'home' }" class="btn btn-success btn-lg uge">
                    home
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { cartLogic } from "./Cart.logic.js";

export default cartLogic;
</script>

<style lang="scss" scoped src="./Cart.scss"></style>
