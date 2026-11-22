<template>
    <div class="cont">
        <h1>Would you like something?</h1>
        <p>Types Selected: {{ selectedTypesLenght }}</p>
        <div class="types-container">
            <ul>
                <li v-for="type in alltypes" :key="`type_id${type.id}`">
                    <TypeComp :type="type" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { typeSelectionLogic } from "./TypeSelection.logic.js";

export default typeSelectionLogic;
</script>

<style lang="scss" scoped src="./TypeSelection.scss"></style>
