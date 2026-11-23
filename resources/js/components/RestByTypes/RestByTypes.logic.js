import { mapGetters, mapState } from "vuex";
import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { PaginateEl } from "../PaginateEl";
import { t } from "../../utils";

const restByTypesLogic = {
    name: "RestByTypes",
    components: { FoodImage, Icon, PaginateEl },
    computed: {
        ...mapState(["restByTypes", "restPagination"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {
        menuRoute: rest => ({ name: "restMenu", params: { slug: rest.slug } }),
        t
    }
};

export { restByTypesLogic };
