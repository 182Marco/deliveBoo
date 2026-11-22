// the cloche mark (public/images/foodsy-mark.svg, the only copy of the logo) and the wordmark
const LOGO_SRC = "/images/foodsy-mark.svg";

const logoLogic = {
    name: "Logo",
    props: {
        // cream wordmark for dark backgrounds; the mark keeps the brand colour
        inverse: Boolean
    },
    data: () => ({
        src: LOGO_SRC
    })
};

export { logoLogic };
