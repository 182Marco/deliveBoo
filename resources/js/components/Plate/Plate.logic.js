import { mapMutations, mapState } from "vuex";
import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { QuantityStepper } from "../QuantityStepper";
import { canAddPlate, countPortions, formatPrice } from "../../utils";

// one dish of the menu; the "one restaurant per order" warning lives once in RestMenu
const plateLogic = {
    name: "Plate",
    components: { FoodImage, Icon, QuantityStepper },
    props: {
        plate: Object
    },
    computed: {
        ...mapState(["cart"]),
        portionsInCart() {
            return countPortions(this.cart, this.plate);
        },
        price() {
            return formatPrice(this.plate.price);
        }
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate", "changeWarn"]),
        // plates of another restaurant open the warning instead
        addToCart() {
            if (canAddPlate(this.cart, this.plate)) this.addPlate(this.plate);
            else this.changeWarn();
        },
        // removePlate wants the very object in the bag: after a reload the menu holds new copies
        removeOne() {
            const portion = this.cart.find(e => e.id == this.plate.id);
            if (portion) this.removePlate(portion);
        }
    }
};

export { plateLogic };
