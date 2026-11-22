// photo of a dish or restaurant: lazy by default, a branded placeholder when missing or broken
const PLACEHOLDER = "/images/foodsy-mark.svg";

const foodImageLogic = {
    name: "FoodImage",
    props: {
        src: { type: String, default: "" },
        alt: { type: String, default: "" },
        // above the fold (hero, cover): load immediately
        eager: Boolean
    },
    data: () => ({
        failed: false,
        placeholder: PLACEHOLDER
    }),
    computed: {
        hasPhoto() {
            return !!this.src && !this.failed;
        }
    },
    watch: {
        src() {
            this.failed = false;
        }
    },
    methods: {
        onError() {
            this.failed = true;
        }
    }
};

export { foodImageLogic };
