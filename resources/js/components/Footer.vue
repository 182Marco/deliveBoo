<template>
    <footer class="footer">
        <div class="cont">
            this is the footer of the site made by the best team
        </div>
    </footer>
</template>

<script>
export default {
    name: "Footer"
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

footer.footer {
    text-align: center;
    margin-top: 100px;
}
</style>
