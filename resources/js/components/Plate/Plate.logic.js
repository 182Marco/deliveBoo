import { mapMutations, mapState } from "vuex";
import { Warning } from "../Warning";
import { canAddPlate, countPortions, scrollToTop } from "../../utils";

const IMG_NOT_FOUND = "https://mantenimientocode.xyz/images/not-found.jpg";

const plateLogic = {
    name: "Plate",
    components: { Warning },
    props: {
        plate: Object
    },
    data: () => ({
        imgNotFound: IMG_NOT_FOUND
    }),
    computed: {
        ...mapState(["cart", "warn"]),
        portionsInCart() {
            return countPortions(this.cart, this.plate);
        }
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate", "changeWarn"]),
        // plates of another restaurant open the warning instead
        addToCart() {
            if (canAddPlate(this.cart, this.plate)) this.addPlate(this.plate);
            else this.changeWarn();
        },
        scrollToTop
    }
};

export { plateLogic };
