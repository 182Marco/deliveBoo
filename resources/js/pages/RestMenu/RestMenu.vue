<template>
    <!-- render only once the menu is loaded -->
    <div v-if="restaurant.location && restaurant.menu" class="cont">
        <div class="items-box">
            <div class="img-box">
                <img :src="restaurant.location.img" :alt="restaurant.location.name" />
            </div>
            <div class="txt box">
                <h1>
                    Restaurant: <em>{{ restaurant.location.name }}</em>
                </h1>
                <p>
                    <strong class="little-title">address: </strong>
                    {{ restaurant.location.address }},
                    {{ restaurant.location.city }}
                </p>
                <p>
                    <strong class="little-title">phone: </strong>
                    {{ restaurant.location.phone }}
                </p>
                <p class="rating">
                    <strong class="little-title">rated: </strong>
                    <StarsBox :average="stars" />
                </p>
                <strong class="little-title">Types: </strong>
                <span v-for="type in restaurant.types" :key="`tId${type}`" class="label">{{ type }}</span>
            </div>
        </div>
        <h2>Menu:</h2>
        <Plate v-for="(plate, i) in restaurant.menu.data" :key="`_${i}`" :plate="plate" />
        <PaginateEl :paginate="menuPagination" :get-menu="true" :slug="restaurant.location.slug" />
    </div>
</template>

<script>
import { restMenuLogic } from "./RestMenu.logic.js";

export default restMenuLogic;
</script>

<style lang="scss" scoped src="./RestMenu.scss"></style>
