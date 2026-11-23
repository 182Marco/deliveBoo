import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { t } from "../../utils";

// hero of the home page: photos are external links (Unsplash), never hosted by Foodsy
const unsplash = (id, width) => `https://images.unsplash.com/photo-${id}?auto=format&fit=crop&w=${width}&q=75`;

const PHOTOS = {
    main: unsplash("1504674900247-0877df9cc836", 900),
    top: unsplash("1591814468924-caf88d1232e1", 600),
    bottom: unsplash("1621996346565-e3dbc646d9a9", 600)
};

const PERKS = [
    { icon: "store", text: "hero.perkKitchens" },
    { icon: "lock", text: "hero.perkCheckout" },
    { icon: "mail", text: "hero.perkEmail" }
];

const jumbotronLogic = {
    name: "Jumbotron",
    components: { FoodImage, Icon },
    data: () => ({
        photos: PHOTOS,
        perks: PERKS
    }),
    methods: {
        t
    }
};

export { jumbotronLogic };
