<template>
        <main class="container-fluid cover">
            <div class="row align-items-center justify-content-center justify-content-xl-start vh-80">
                <div class="col col-sm-7 text-center text-xl-left col-xl-4 offset-xl-2 bkg">
                    <h1 class="mb-4">the food you love, delivered to your home</h1>
                    <h4 class="pulse mb-5 subtitle-mb">#atYourHomeWithDeliveroo</h4>
                </div>
            </div>
        </main>
</template>


<script>
export default {
    name: "Jumbotron",
}
</script>

<style lang="scss" scoped>

@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

.cover {
        background: url('https://montecristomagazine.com/wp-content/uploads/2021/01/2021FoodPredictions-1.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        margin-bottom: 80px;
}

.bkg {
    background-color: (rgba(46, 51, 51, 0.6));
    border-radius: 50px;
    padding-top: 40px;
    padding-left: 40px;
}

h1 {
    text-transform: uppercase;
}

h1,
h4 {
    color: #ffff;
    font-weight: bold;
    text-shadow: 2px 2px 2px #000;
}

.vh-80 {
        min-height: 84vh;
}



</style>
