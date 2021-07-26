<template>
    <div class="cont">
        <div class="wrap-thanks">
            <div class="thanks">
                <h1>Thank you!</h1>
                <p>Thanks for ordering from us!</p>
                <p>You should receive a confirmation email soon!</p>
                <router-link :to="{ name: 'home' }" class="home"
                    >go to Home</router-link
                >
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "SuccessPage"
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";


.cont {
    margin-top: 80px;
    margin-bottom: 80px;
}

.thanks {
    padding: 30px;
    text-align: center;
}
h1 {
    font-size: 4em;
    font-weight: bold;
    letter-spacing: 3px;
    color: $brand;
    margin: 0;
    margin-bottom: 20px;
}
.thanks p {
    margin: 0;
    font-size: 1.3em;
    color: #434848;
    letter-spacing: 1px;
}
.home {
    color: #fff;
    background: $brand;
    border: none;
    padding: 10px 50px;
    margin: 30px 0;
    border-radius: 30px;
    text-transform: capitalize;
    box-shadow: 0 10px 16px 1px rgba(174, 199, 251, 1);
}
.footer-like {
    margin-top: auto;
    background: #d7e6fe;
    padding: 6px;
    text-align: center;
}
.footer-like p {
    margin: 0;
    padding: 4px;
    color: #5892ff;
    font-family: "Source Sans Pro", sans-serif;
    letter-spacing: 1px;
}
.footer-like p a {
    text-decoration: none;
    color: #5892ff;
    font-weight: 600;
}
@media (min-width: 360px) {
    h1 {
        font-size: 4.5em;
    }
    .home {
        margin-bottom: 20px;
        cursor: pointer;
        transition: transform 0.3s;
        text-decoration: none;
        &:hover {
            transform: scale(1.03);
        }
    }
}
@media (min-width: 600px) {
    .content {
        max-width: 1000px;
        margin: 0 auto;
    }
    .wrap-thanks {
        height: initial;
        max-width: 620px;
        margin: 0 auto;
        margin-top: 50px;
        box-shadow: 4px 8px 40px 8px rgba(88, 146, 255, 0.2);
    }
}
</style>
