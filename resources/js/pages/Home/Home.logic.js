import { Content, Icon, Jumbotron, RestByTypes, TopRated, TypeSelection } from "../../components";
import { t } from "../../utils";

const homeLogic = {
    name: "Home",
    components: { Jumbotron, Content, Icon, TopRated, TypeSelection, RestByTypes },
    methods: {
        t
    }
};

export { homeLogic };
