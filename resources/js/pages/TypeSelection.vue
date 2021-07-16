<template>
    <div class="container">
        <h2>Cercavi qualcosa?</h2>
        <div class="types-container">
            <ul>
                <li>
                    <a href="#">Messicano</a>
                </li>
                <li>
                    <a href="#">Vegano</a>
                </li>
                <li>
                    <a href="#">Giapponese</a>
                </li>
                <li>
                    <a href="#">Fast-food</a>
                </li>
                <li>
                    <a href="#">Pizzeria</a>
                </li>
                <li>
                    <a href="#">Etnico</a>
                </li>
                <li>
                    <a href="#">Sushi</a>
                </li>
                <li>
                    <a href="#">Libanese</a>
                </li>
                <li>
                    <a href="#">Dessert</a>
                </li>
                <li>
                    <a href="#">Greco</a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: "TypeSelection"
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
            }
        }
    }
}
</style>
