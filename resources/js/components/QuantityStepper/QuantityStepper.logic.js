import { Icon } from "../Icon";

// − n +: the parent owns the count and reacts to the two events
const quantityStepperLogic = {
    name: "QuantityStepper",
    components: { Icon },
    props: {
        count: { type: Number, required: true },
        // used in the accessible labels ("Add one Fries")
        item: { type: String, default: "item" },
        small: Boolean
    },
    emits: ["increment", "decrement"]
};

export { quantityStepperLogic };
