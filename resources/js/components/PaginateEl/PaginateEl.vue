<template>
    <nav v-if="paginate.last > 1" class="ui-pagination" aria-label="Pagination">
        <button type="button" class="ui-page" :disabled="isFirst" aria-label="First page" @click="goTo(1)">
            <Icon name="chevrons-left" :size="18" />
        </button>
        <button
            type="button"
            class="ui-page"
            :disabled="isFirst"
            aria-label="Previous page"
            @click="goTo(paginate.current - 1)"
        >
            <Icon name="chevron-left" :size="18" />
        </button>
        <button
            v-for="n in paginate.last"
            :key="`number_${n}`"
            type="button"
            class="ui-page"
            :class="{ 'ui-page--current': n == paginate.current }"
            :aria-current="n == paginate.current ? 'page' : null"
            @click="goTo(n)"
        >
            {{ n }}
        </button>
        <button
            type="button"
            class="ui-page"
            :disabled="isLast"
            aria-label="Next page"
            @click="goTo(paginate.current + 1)"
        >
            <Icon name="chevron-right" :size="18" />
        </button>
        <button type="button" class="ui-page" :disabled="isLast" aria-label="Last page" @click="goTo(paginate.last)">
            <Icon name="chevrons-right" :size="18" />
        </button>
    </nav>
</template>

<script>
import { paginateElLogic } from "./PaginateEl.logic.js";

export default paginateElLogic;
</script>
