<template>
    <section>
        <Jumbotron />
        <div class="cont">
            <div class="main">
                <TopRated />
                <TypeSelection />
                <RestByTypes />
            </div>
        </div>
    </section>
</template>

<script>
import { homeLogic } from "./Home.logic.js";

export default homeLogic;
</script>

<style lang="scss" scoped src="./Home.scss"></style>
