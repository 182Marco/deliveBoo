const MAX_STARS = 5;

// average: integer from 0 to 5 (full stars), the rest are empty stars
const starsBoxLogic = {
    name: "StarsBox",
    props: {
        average: Number,
        topList: Boolean
    },
    computed: {
        emptyStars() {
            return MAX_STARS - this.average;
        }
    }
};

export { starsBoxLogic };
