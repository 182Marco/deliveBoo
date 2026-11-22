<template>
    <main>
        <div class="cont">
            <h2>The Deliveroo selection</h2>
            <div class="current-choice-box">
                <h4>
                    <span v-show="typesSelected.length"> current choice:</span>
                    <span v-for="type in selectedTypes" :key="`t${type.id}`">
                        <em class="type-name-list">/ {{ type.name }}</em>
                    </span>
                </h4>
            </div>
            <div class="boxes">
                <div
                    v-for="type in alltypes"
                    :key="`type_index${type.id}`"
                    class="box"
                    @click="selectSingleType(type.id)"
                >
                    <img :src="type.img" :alt="type.name" />
                    <div class="text-shadow" :class="{ clicked: isSelected(type.id) }">
                        {{ type.name }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { contentLogic } from "./Content.logic.js";

export default contentLogic;
</script>

<style lang="scss" scoped src="./Content.scss"></style>
