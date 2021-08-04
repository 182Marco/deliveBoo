<template>
    <div>
        <h2>Top 12 rated restaurants:</h2>
        <section>
            <div
                v-for="(rest, i) in topRest"
                :key="`rest__id${rest.id}`"
                class="box"
            >
                <img class="rest-img" :src="rest.img" :alt="rest.name" />
                <div @click="goToRest(rest.slug)" class="layer"></div>
                <i
                    @click="goToRest(rest.slug)"
                    v-if="i == 0"
                    class="fas fa-medal gold"
                ></i>
                <i
                    @click="goToRest(rest.slug)"
                    v-if="i == 1"
                    class="fas fa-medal silver"
                ></i>
                <i
                    @click="goToRest(rest.slug)"
                    v-if="i == 2"
                    class="fas fa-medal bronze"
                ></i>
                <StarsBox
                    @click.native="goToRest(rest.slug)"
                    :average="rest.average"
                    :topList="true"
                />
                <p @click="goToRest(rest.slug)" class="name">{{ rest.name }}</p>
            </div>
        </section>
    </div>
</template>

<script>
import StarsBox from "./StarsBox.vue";
export default {
    name: "TopRated",
    components: {
        StarsBox
    },
    data() {
        return {
            topRest: []
        };
    },
    created() {
        this.getTop();
    },
    methods: {
        getTop() {
            axios
                .get("http://127.0.0.1:8000/api/restaurantsTopRated")
                .then(r => {
                    this.topRest = r.data.map(e => ({
                        ...e,
                        average: Math.ceil(parseFloat(e.average))
                    }));
                })
                .catch(r => console.log(r));
        },
        goToRest(restSlug) {
            this.$router.push({ name: "restMenu", params: { slug: restSlug } });
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
    font-weight: 700;
    color: $txt-col;
}
section {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    margin-top: 30px;
    margin-bottom: 110px;
    .box {
        position: relative;
        height: 150px;
        border-radius: 5px;
        overflow: hidden;
        width: calc(100% / 4 - 8px);
        @include media-desk-first(s-desktop) {
            width: calc(100% / 3 - 8px);
        }
        @include media-desk-first(l-tablet) {
            width: calc(100% / 2 - 8px);
        }
        @include media-desk-first(xs-tablet) {
            width: calc(100% / 1 - 8px);
        }
        margin-right: 8px;
        margin-bottom: 15px;
        .rest-img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
        .layer {
            cursor: pointer;
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.15);
        }
        .fa-medal {
            cursor: pointer;
            font-size: 3rem;
            position: absolute;
            top: 5%;
            left: 5%;
            &.gold {
                color: gold;
            }
            &.silver {
                color: silver;
            }
            &.bronze {
                color: #cc6633;
            }
        }
        .name {
            cursor: pointer;
            position: absolute;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            // create black border to text-> more readable
            text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
            font-size: 1.2rem;
            line-height: 1.1rem;
            font-weight: 700;
        }
    }
}
</style>
