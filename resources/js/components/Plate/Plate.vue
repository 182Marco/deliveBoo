<template>
    <div>
        <div class="menu-wrap">
            <article class="plate card">
                <h3 class="mb-1">{{ plate.name }}</h3>
                <div class="img-box">
                    <img :src="plate.img || imgNotFound" :alt="plate.name" />
                </div>
                <p><strong>Description: </strong> {{ plate.description }}</p>
                <p><strong>Ingredients: </strong> {{ plate.ingredients }}</p>
                <p>
                    <strong>Available: </strong>
                    <span>{{ plate.visible ? " yes" : "no" }}</span>
                </p>
                <p><strong>Price: </strong>{{ plate.price }} €</p>
                <div class="btns-box">
                    <button class="btn btn-success btn-small" @click="addToCart">
                        + Add a portion to cart
                    </button>
                    <button v-if="portionsInCart" class="btn btn-success btn-small" @click="removePlate(plate)">
                        - Remove a portion from cart
                    </button>
                    <router-link :to="{ name: 'cart' }" class="cart-btn btn-success btn btn-small" @click="scrollToTop">
                        check the cart
                    </router-link>
                </div>
                <p>
                    <strong class="item-in-cart">portions already in cart: {{ portionsInCart }} </strong>
                </p>
            </article>
            <!-- one per plate, as before: they overlap in the same fixed position -->
            <Warning v-if="warn" />
        </div>
    </div>
</template>

<script>
import { plateLogic } from "./Plate.logic.js";

export default plateLogic;
</script>

<style lang="scss" scoped src="./Plate.scss"></style>
