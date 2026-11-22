<template>
    <section class="navigation">
        <button :disabled="isFirst" @click="goTo(1)">first</button>
        <button :disabled="isFirst" @click="goTo(paginate.current - 1)">prev</button>
        <button
            v-for="n in paginate.last"
            :key="`number_${n}`"
            class="number"
            :class="{ active: n == paginate.current }"
            @click="goTo(n)"
        >
            {{ n }}
        </button>
        <button :disabled="isLast" @click="goTo(paginate.current + 1)">next</button>
        <button :disabled="isLast" @click="goTo(paginate.last)">last</button>
    </section>
</template>

<script>
import { paginateElLogic } from "./PaginateEl.logic.js";

export default paginateElLogic;
</script>

<style lang="scss" scoped src="./PaginateEl.scss"></style>
