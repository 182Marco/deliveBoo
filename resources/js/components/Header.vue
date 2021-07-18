<template>
    <header>
        <div class="cont">
            <nav>
                <img src="../img/logo.png" alt="logo" />
                <ul>
                    <li>
                        <router-link :to="{ name: 'collabora' }"
                            >Collabora con noi</router-link
                        >
                    </li>
                    <li>
                        <router-link :to="{ name: 'log' }"
                            >Registrati o accedi</router-link
                        >
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</template>

<script>
export default {
    name: "Header"
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

header {
    background-color: $brand;
    padding: 40px 0;
    margin-bottom: 100px;
    nav {
        @include flex(row, space-between, center);
        img {
            width: 140px;
        }
        ul {
            li {
                margin-right: 20px;
                a {
                    font-weight: 700;
                    color: $white;
                }
            }
        }
    }
}

.active {
    color: $col2;
}
</style>
plates
