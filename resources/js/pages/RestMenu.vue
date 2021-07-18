<template>
    <div class="cont">
        <h1>Restaurant {{ restaurant.name }}</h1>
        <h2>the restaurant plates:</h2>
    </div>
</template>

<script>
export default {
    name: "RestMenu",
    data() {
        return {
            restaurant: {
                name: "Il Gabbiano"
            }
        };
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
</style>
