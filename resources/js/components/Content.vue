<template>
    <main>
        <div class="cont">
            <h2>The Deliveroo selection</h2>
            <div class="current-choice-box">
                <h4 v-if="selcetedId">
                    current choice:
                    {{ alltypes.filter(e => e.id == selcetedId)[0].name }}
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
                        :class="{ clicked: selcetedId == type.id }"
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
        return {
            clicked: null,
            selcetedId: this.$route.params.id
        };
    },
    computed: {
        ...mapState(["alltypes"])
    },
    methods: {
        // programmatic navigation with vue router
        selectSingleType(restTypeId) {
            if (this.selcetedId != restTypeId) {
                this.$store.commit("cleanSelectedTypes");
                this.$store.commit("addType", restTypeId);

                this.$router.push({
                    name: "singleType",
                    params: { id: restTypeId }
                });
            }
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
    padding: 50px 15px;
    margin-top: 50px;
    .cont {
        // create fixed height so the template willl not
        // br pushed whwen element dyspayed
        .current-choice-box {
            height: 40px;
        }
        h2,
        h4 {
            font-weight: bold;
            margin-left: 5px;
            margin-bottom: 10px;
        }
        h4 {
            color: $col2;
            @include media-desk-first(tablet) {
                font-size: 20px;
                line-height: 20px;
            }
        }
        .boxes {
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
                padding: 0 50%;
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

@keyframes pulse {
    0% {
        transform: scale(1);
    }

    5% {
        transform: scale(1.05);
    }

    10% {
        transform: scale(1);
    }
    15% {
        transform: scale(1.05);
    }
    20% {
        transform: scale(1);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes tremble {
    0% {
        transform: rotate(0deg);
    }
    5% {
        transform: rotate(3deg);
    }
    10% {
        transform: rotate(-3deg);
    }
    15% {
        transform: rotate(3deg);
    }
    20% {
        transform: rotate(-3deg);
    }
    25% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(0deg);
    }
}
</style>
