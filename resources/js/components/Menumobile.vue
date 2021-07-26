<template>
    <section v-if="menuMobile">
        <header>
            <img src="/../../images/logoColBrand.png" alt="delivero Logo" />
            <p class="x" @click="$emit('closeMen')">x</p>
        </header>
        <div class="btn-box">
            <a class="main-btn" href="http://127.0.0.1:8000/login">
                Registrati o accedi
            </a>
        </div>
        <main>
            <ul>
                <li>
                    <div>
                        <a href="http://127.0.0.1:8000/register">
                            <i class="fas fa-utensils"></i>Aggiungi il tuo
                            ristorante
                        </a>
                    </div>
                    <p>
                        >
                    </p>
                </li>
                <li>
                    <div>
                        <a href="http://127.0.0.1:8000/register"
                            ><i class="fas fa-store-alt"></i>Crea un account
                            aziendale
                        </a>
                    </div>
                    <p>
                        >
                    </p>
                </li>
                <li>
                    <div>
                        <a href="#"><i class="fas fa-question"></i>FAQ</a>
                    </div>
                    <p>
                        >
                    </p>
                </li>
            </ul>
            <!-- language below -->
            <ul class="lang-list">
                <li>
                    <div>
                        <a href="#">Italiano</a>
                    </div>
                    <p>
                        >
                    </p>
                </li>
                <li>
                    <div>
                        <a href="#">Italy</a>
                    </div>
                    <p>
                        >
                    </p>
                </li>
            </ul>
        </main>
    </section>
</template>

<script>
import { mapState } from "vuex";
export default {
    name: "MenuMobile",
    props: {},
    data() {
        return {};
    },
    computed: {
        ...mapState(["menuMobile"])
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

section {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #f9fafa;
    z-index: 10;
    animation: fade-in 0.4s;
    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ebebeb;
        padding: 13px 20px;
        background-color: white;
        img {
            width: 130px;
        }
        .x {
            font-size: 2.3rem;
            color: $brand;
            cursor: pointer;
        }
    }
    .btn-box {
        padding: 20px;
        display: flex;
        justify-content: center;
        background: #f9fafa;
        .main-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 17px;
            margin-bottom: 30px;
            background-color: $brand;
            color: white;
            width: 100%;
            height: 60px;
            border-radius: 5px;
            font-size: 1.2rem;
            font-weight: 700;
            border: none;
            transition: background-color 0.4s;
            &:hover {
                text-decoration: none;
                background-color: $col2;
            }
        }
    }
    li {
        padding: 30px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 50px;
        border-bottom: 1px solid #ebebeb;
        background: #fff;
        &:first-child {
            border-top: 1px solid #ebebeb;
        }
        a {
            i {
                display: inline-block;
                margin-right: 15px;
                color: #abadad;
                &.fa-question {
                    display: inline-flex;
                    justify-content: center;
                    align-items: center;
                    border: 3px solid #abadad;
                    height: 31px;
                    width: 31px;
                    border-radius: 50%;
                    font-size: 1.2rem;
                }
            }
            color: #494949;
            font-size: 1.4rem;
            text-decoration: none;
            &:hover {
                color: $brand;
            }
        }
        p {
            font-size: 1.7rem;
            color: $brand;
        }
    }
}

@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

.lang-list {
    padding: 0 20px;
    position: absolute;
    bottom: 3%;
    width: 100%;
    li {
        border: 1px solid #ebebeb;
        border-radius: 4px;
        a:hover {
            color: #494949;
        }
        &:last-child {
            margin-top: 20px;
        }
        p {
            color: #494949;
            &:hover {
                cursor: pointer;
            }
        }
    }
}
</style>
