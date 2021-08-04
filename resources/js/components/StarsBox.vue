<template>
    <div class="stars-box" :class="{ top_list: topList }">
        <i
            v-for="fasStar in average"
            :key="`fasStar${fasStar}`"
            class="fas fa-star"
        ></i>
        <i
            v-for="farStar in 5 - average"
            :key="`farStar${farStar}`"
            class="far fa-star"
        ></i>
    </div>
</template>

<script>
export default {
    name: "StarsBox",
    props: {
        average: Number,
        topList: Boolean
    }
};
</script>

<style lang="scss" scoped>
.stars-box {
    display: inline-block;
    cursor: pointer;
    i {
        color: gold;
    }
}
.top_list {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>
