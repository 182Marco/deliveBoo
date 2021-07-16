<template>
    <div class="container">
        <h2>Cercavi qualcosa?</h2>
        <div class="types-container">
            <ul>
                <li v-for="type in types" :key="type.id">
                    <TypeComp :type="type" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import TypeComp from "./TypeComp.vue";

export default {
    name: "TypeSelection",
    components: {
        TypeComp
    },
    props: {
        types: Array
    },
    data() {
        return {};
    },
    methods: {}
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

h2 {
    font-size: 40px;
}

.types-container {
    display: flex;
    padding: 20px 0px;

    ul {
        li {
            a {
                font-size: 28px;
                font-weight: 600;
                text-decoration: none;
                background: #f9fafa;
                color: #2e3333;
                margin-right: 10px;
                padding: 5px;
                margin-bottom: 10px;
                &.clicked {
                    color: blue;
                }
            }
        }
    }
}
</style>
