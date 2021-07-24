<template>
    <main>
        <div class="cont">
            <h2>The Deliveroo selection</h2>
            <div class="boxes">
                <div class="box">
                    <img src="https://www.generazionepost.it/wp-content/uploads/2020/01/Cibo-Messicano.jpg" alt="">
                    <div class="text-shadow">Mexican</div>
                </div>
                <div class="box">
                    <img src="https://www.calcionewsweb.it/wp-content/uploads/2019/09/dieta-vegana.jpg" alt="">
                    <div class="text-shadow">Vegan</div>
                </div>
                <div class="box">
                    <img src="https://www.ilgiornaledelcibo.it/wp-content/uploads/2015/03/cultura-culinaria-giapponese.jpg" alt="">
                    <div class="text-shadow">Japanese</div>
                </div>
                <div class="box">
                    <img src="https://www.gustorotondo.it/wp-content/uploads/2019/02/Ristoranti-stellati-Veneto-2019-indicazioni.jpg" alt="">
                    <div class="text-shadow">Starred</div>
                </div>
                <div class="box">
                    <img src="https://static.ohga.it/wp-content/uploads/sites/24/2019/03/cibo-nei-fast-food-qualit%C3%A0-peggiorata.jpg" alt="">
                    <div class="text-shadow">Fast Food</div>
                </div>
                <div class="box">
                    <img src="https://www.pizzanapoletana.org/struttura/pagine_bicolor/mobile/disciplinare_avpn_2.jpg" alt="">
                    <div class="text-shadow">Pizza</div>
                </div>
                <div class="box">
                    <img src="http://www.scienze-naturali.com/wp-content/uploads/2016/09/menu-etnici-taste14-1024x683.jpg" alt="">
                    <div class="text-shadow">Ethnic</div>
                </div>
                <div class="box">
                    <img src="https://www.foodweb.it/wp-content/uploads/2020/06/cibo-italiano.jpg" alt="">
                    <div class="text-shadow">Italian</div>
                </div>
                <div class="box">
                    <img src="https://www.viaggi-estate.com/wp-content/uploads/2016/05/spagna-cibo-paella.jpg" alt="">
                    <div class="text-shadow">Spanish</div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "Content",
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
    padding: 50px 0;
    margin-top: 50px;
    .cont {
        h2 {
            font-weight: bold;
            margin-left: 5px;
            margin-bottom: 20px;
        }
        .boxes {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }
        .box {
            width: calc(100% / 3);
            padding: 10px;
            height: 250px;
            position: relative;
            &:hover {
                cursor: pointer;
            }
            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 10px;
                overflow: hidden;
            }
            .text-shadow {
                color: #fff;
                font-size: 30px;
                font-weight: bold;
                text-shadow: 2px 2px 2px #000;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                position: absolute;
            }
        }
    }
}

</style>