import { Icon } from "../Icon";
import { t } from "../../utils";

// − n +: the parent owns the count and reacts to the two events
const quantityStepperLogic = {
    name: "QuantityStepper",
    components: { Icon },
    props: {
        count: { type: Number, required: true },
        // used in the accessible labels ("Add one Fries"); empty: "item"
        item: { type: String, default: "" },
        small: Boolean
    },
    emits: ["increment", "decrement"],
    computed: {
        itemLabel() {
            return this.item || t("stepper.item");
        }
    },
    methods: {
        t
    }
};

export { quantityStepperLogic };
