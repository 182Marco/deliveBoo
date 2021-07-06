<template>
    <div class="cont">
        <h1>error: 404</h1>
        <h2>
            We're so sorry...what you typed in doesn't match a resource on the
            server
        </h2>
    </div>
</template>

<script>
export default {
    name: "NotFound"
};
</script>

<style lang="scss" scoped>
h1 {
    color: red;
}
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";
</style>
