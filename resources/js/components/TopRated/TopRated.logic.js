import { StarsBox } from "../StarsBox";
import { fetchTopRated } from "../../api";
import { withStarsAverage } from "./TopRated.utils.js";

// medal classes of the first three positions
const MEDALS = ["gold", "silver", "bronze"];

const topRatedLogic = {
    name: "TopRated",
    components: { StarsBox },
    data: () => ({
        topRest: [],
        medals: MEDALS
    }),
    async created() {
        try {
            this.topRest = withStarsAverage(await fetchTopRated());
        } catch (error) {
            console.error(error);
        }
    },
    methods: {
        goToRest(slug) {
            this.$router.push({ name: "restMenu", params: { slug } });
        }
    }
};

export { topRatedLogic };
