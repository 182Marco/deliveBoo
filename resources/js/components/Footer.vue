<template>
    <!-- do not display if there's the warn (you can't purchese from to restaurant in the same order) -->
    <footer v-show="!warn" class="footer">
        <div class="cont">
            <div class="card-container">
                <div class="card">
                    <h3>Explore Deliveroo</h3>
                    <ul>
                        <li>
                            <a href="#">Investors</a>
                        </li>
                        <li>
                            <a href="#">About Us</a>
                        </li>
                        <li>
                            <a href="#">Takeaway</a>
                        </li>
                        <li>
                            <a href="#">More</a>
                        </li>
                        <li>
                            <a href="#">Pressroom</a>
                        </li>
                        <li>
                            <a href="#">Foodscene blog</a>
                        </li>
                        <li>
                            <a href="#">Engineering blog</a>
                        </li>
                        <li>
                            <a href="#">Design</a>
                        </li>
                        <li>
                            <a href="#">Careers</a>
                        </li>
                        <li>
                            <a href="#">Become a rider</a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Legal</h3>
                    <ul>
                        <li>
                            <a href="#">Terms and conditions</a>
                        </li>
                        <li>
                            <a href="#">Privacy</a>
                        </li>
                        <li>
                            <a href="#">Cookies</a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Help</h3>
                    <ul>
                        <li>
                            <a href="#">Contact</a>
                        </li>
                        <li>
                            <a href="#">FAQs</a>
                        </li>
                        <li>
                            <a href="#">Cuisines</a>
                        </li>
                        <li>
                            <a href="#">Site map</a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Take Deliveroo with you</h3>
                    <ul>
                        <li>
                            <a href="#" class="store">
                                <div>
                                    <img
                                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Download_on_the_App_Store_Badge_IT_RGB_blk.svg/2560px-Download_on_the_App_Store_Badge_IT_RGB_blk.svg.png"
                                        alt="apple-store"
                                    />
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="store">
                                <div>
                                    <img
                                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Google_Play_Store_badge_IT.svg/1024px-Google_Play_Store_badge_IT.svg.png"
                                        alt="play-store"
                                    />
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="icons-copy">
                <ul class="icons">
                    <li>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </li>
                    <li>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </li>
                </ul>
                <div class="copyright">&copy; 2021 Deliveroo</div>
            </div>
        </div>
    </footer>
</template>

<script>
import { mapState } from "vuex";
export default {
    name: "Footer",
    computed: {
        ...mapState(["warn"])
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

footer.footer {
    text-align: center;
    margin-top: 100px;
    background: #2e3333;
    .cont {
        .card-container {
            display: flex;
            width: 100%;
            padding: 15px 0;
            @include media-desk-first(xs-desktop) {
                flex-direction: column;
                align-items: center;
                padding: 10px;
            }
            .card {
                text-align: left;
                width: calc((100% / 4) - 10px);
                margin: 0 5px;
                background: #434848;
                padding: 20px;
                border-radius: 5px;
                @include media-desk-first(xs-desktop) {
                    width: 94vw;
                    text-align: center;
                    margin-bottom: 15px;
                    font-size: 1.3rem;
                }

                h3 {
                    color: white;
                    margin-bottom: 10px;
                    font-size: 18px;
                    font-weight: 700;
                    @include media-desk-first(xs-desktop) {
                        font-size: 1.6rem;
                    }
                }

                ul {
                    li {
                        margin-bottom: 5px;
                        display: block;

                        a {
                            color: white;
                            text-decoration: none;
                            font-size: 15px;
                            @include media-desk-first(xs-desktop) {
                                font-size: 1.3rem;
                            }

                            div {
                                height: 40px;
                                width: 135px;
                                margin-bottom: 10px;
                                img {
                                    max-width: 100%;
                                    height: auto;
                                }
                            }
                        }
                    }
                    li:last-child {
                        margin-bottom: 0px;
                    }
                }
            }
        }

        .icons-copy {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0px 5px 20px;
            @include media-desk-first(xs-desktop) {
                padding: 0px 20px 20px;
            }
            .icons {
                display: flex;
                li {
                    margin-right: 20px;
                    a {
                        i {
                            font-size: 20px;
                            color: white;
                        }
                    }
                }
            }
            .copyright {
                color: #828585;
            }
        }
    }
}
</style>
