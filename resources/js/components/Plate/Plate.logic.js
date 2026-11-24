import { mapActions, mapGetters } from "vuex";
import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { QuantityStepper } from "../QuantityStepper";
import { formatPrice, t } from "../../utils";

// one dish of the menu; the "one restaurant per order" warning lives once in RestMenu
const plateLogic = {
    name: "Plate",
    components: { FoodImage, Icon, QuantityStepper },
    props: {
        plate: Object
    },
    computed: {
        ...mapGetters(["portionsOf"]),
        portionsInCart() {
            return this.portionsOf(this.plate.id);
        },
        price() {
            return formatPrice(this.plate.price);
        },
        // the API says whether it can be ordered now (availability and season); older payloads only have visible
        orderable() {
            return this.plate.orderable ?? Boolean(this.plate.visible);
        },
        unavailableLabel() {
            return t(this.plate.availability === "sold_out" ? "dish.soldOut" : "dish.unavailable");
        }
    },
    methods: {
        ...mapActions({ addOneToCart: "addOne", removeOneFromCart: "removeOne" }),
        t,
        // plates of another restaurant open the warning instead (decided by the store)
        addToCart() {
            this.addOneToCart(this.plate);
        },
        removeOne() {
            this.removeOneFromCart(this.plate);
        }
    }
};

export { plateLogic };
