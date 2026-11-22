import { mapState } from "vuex";

const LINK_CARDS = [
    {
        title: "Explore Deliveroo",
        links: [
            "Investors",
            "About Us",
            "Takeaway",
            "More",
            "Pressroom",
            "Foodscene blog",
            "Engineering blog",
            "Design",
            "Careers",
            "Become a rider"
        ]
    },
    { title: "Legal", links: ["Terms and conditions", "Privacy", "Cookies"] },
    { title: "Help", links: ["Contact", "FAQs", "Cuisines", "Site map"] }
];

const STORE_BADGES = [
    {
        src: "https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Download_on_the_App_Store_Badge_IT_RGB_blk.svg/2560px-Download_on_the_App_Store_Badge_IT_RGB_blk.svg.png",
        alt: "apple-store"
    },
    {
        src: "https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Google_Play_Store_badge_IT.svg/1024px-Google_Play_Store_badge_IT.svg.png",
        alt: "play-store"
    }
];

const SOCIAL_ICONS = ["fab fa-facebook", "fab fa-twitter", "fab fa-instagram"];

const footerLogic = {
    name: "Footer",
    data: () => ({
        linkCards: LINK_CARDS,
        storeBadges: STORE_BADGES,
        socialIcons: SOCIAL_ICONS
    }),
    computed: {
        ...mapState(["warn"])
    }
};

export { footerLogic };
