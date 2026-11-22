import { mapGetters, mapState } from "vuex";
import { PaginateEl } from "../PaginateEl";

const restByTypesLogic = {
    name: "RestByTypes",
    components: { PaginateEl },
    computed: {
        ...mapState(["restByTypes", "restPagination"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {
        menuRoute: rest => ({ name: "restMenu", params: { slug: rest.slug } })
    }
};

export { restByTypesLogic };
