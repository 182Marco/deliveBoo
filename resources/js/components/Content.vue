<template>
    <main>
        <div class="cont">
            <h2>The Deliveroo selection</h2>
            <div class="current-choice-box">
                <h4>
                    <span v-show="typesSelected.length"> current choice:</span>
                    <span
                        v-for="(type, i) in alltypes"
                        :key="`t${i}`"
                        v-show="typesSelected.includes(type.id)"
                    >
                        <em class="type-name-list">/ {{ type.name }}</em>
                    </span>
                </h4>
            </div>
            <div class="boxes">
                <div
                    @click="selectSingleType(type.id)"
                    class="box"
                    v-for="(type, i) in alltypes"
                    :key="`type_index${i}`"
                >
                    <img :src="type.img" :alt="type.name" />
                    <div
                        class="text-shadow"
                        :class="{ clicked: typesSelected.includes(type.id) }"
                    >
                        {{ type.name }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { mapState } from "vuex";
export default {
    name: "Content",
    data() {
        return {};
    },
    computed: {
        ...mapState(["alltypes", "typesSelected"])
    },
    methods: {
        // programmatic navigation with vue router
        selectSingleType(restTypeId) {
            this.$store.commit("cleanSelectedTypes");
            this.$store.commit("addType", restTypeId);
            this.$router.push({
                name: "singleType",
                params: { id: restTypeId }
            });
            window.scrollTo(0, 0);
        }
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

main {
    background: #ffeae4;
    padding: 25px 15px;
    margin-top: 50px;
    .cont {
        h2,
        h4 {
            font-weight: bold;
            margin-left: 5px;
            margin-bottom: 7px;
        }
        h4 {
            font-weight: 700;

            @include media-desk-first(tablet) {
                font-size: 25px;
                line-height: 25px;
            }
        }
        .type-name-list {
            color: $col2;
        }
        .boxes {
            margin-top: 5px;
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }
        .box {
            width: calc(100% / 3);
            @include media-desk-first(desktop) {
                width: calc(100% / 2);
            }
            @include media-desk-first(s-tablet) {
                width: calc(100% / 1);
            }
            padding: 10px;
            height: 250px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            &:hover {
                cursor: pointer;
            }
            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 10px;
                overflow: hidden;
                transition: transform 0.3s;
            }
            .text-shadow {
                color: #fff;
                font-size: 30px;
                font-weight: bold;
                text-shadow: 2px 2px 2px #000;
                margin: 0 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                line-height: 30px;
                position: absolute;
                cursor: pointer;
                transition: transform 0.3s;
                &:hover {
                    transform: scale(1.07);
                }
                &.clicked {
                    text-shadow: 4px 4px 4px #000;
                    animation: tremble 1.3s infinite;
                }
            }
        }
    }
}
</style>
