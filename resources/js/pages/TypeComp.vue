<template>
    <div>
        <a
            :class="{ clicked: checked }"
            @click="sendTypeSelected(type.id)"
            href="#"
            >{{ type.name }}</a
        >
    </div>
</template>

<script>
export default {
    name: "TypeComp",
    props: {
        type: Object
    },
    data() {
        return {
            checked: false
        };
    },
    created() {},
    methods: {
        sendTypeSelected(id) {
            this.checked = !this.checked;
            this.checked
                ? this.$emit("clickedType", this.type.id)
                : this.$emit("unClickedType", this.type.id);
        }
    }
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
