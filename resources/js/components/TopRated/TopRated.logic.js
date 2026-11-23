import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { StarsBox } from "../StarsBox";
import { fetchTopRated } from "../../api";
import { t } from "../../utils";
import { rankLabel, withStarsAverage } from "./TopRated.utils.js";

// share of the visible rail moved by the arrows
const SCROLL_STEP = 0.85;

const topRatedLogic = {
    name: "TopRated",
    components: { FoodImage, Icon, StarsBox },
    data: () => ({
        topRest: []
    }),
    async created() {
        try {
            this.topRest = withStarsAverage(await fetchTopRated());
        } catch (error) {
            console.error(error);
        }
    },
    methods: {
        menuRoute: rest => ({ name: "restMenu", params: { slug: rest.slug } }),
        rank: rankLabel,
        t,
        // the rail stays swipeable; the arrows help mouse users
        scrollRail(direction) {
            const rail = this.$refs.rail;
            rail.scrollBy({ left: direction * rail.clientWidth * SCROLL_STEP, behavior: "smooth" });
        }
    }
};

export { topRatedLogic };
