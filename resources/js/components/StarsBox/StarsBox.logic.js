import { Icon } from "../Icon";

const MAX_STARS = 5;

// average: integer from 0 to 5 (full stars), the rest are empty stars
const starsBoxLogic = {
    name: "StarsBox",
    components: { Icon },
    props: {
        average: { type: Number, default: 0 },
        size: { type: Number, default: 16 }
    },
    data: () => ({
        max: MAX_STARS
    })
};

export { starsBoxLogic };
