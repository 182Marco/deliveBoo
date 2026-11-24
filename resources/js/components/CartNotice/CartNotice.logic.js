import { mapActions, mapState } from "vuex";
import { Icon } from "../Icon";
import { t } from "../../utils";
import { noticeText, noticeTone } from "./CartNotice.utils.js";

// how long a message about the cart stays on screen
const NOTICE_MS = 8000;

// messages about the cart (merge at login, plates removed, offline, errors): fixed at the bottom
const cartNoticeLogic = {
    name: "CartNotice",
    components: { Icon },
    computed: {
        ...mapState(["cartNotices"])
    },
    watch: {
        cartNotices(notices, before = []) {
            notices
                .filter(notice => !before.some(e => e.id === notice.id))
                .forEach(notice => setTimeout(() => this.dropCartNotice(notice.id), NOTICE_MS));
        }
    },
    methods: {
        ...mapActions(["dropCartNotice"]),
        t,
        textOf: noticeText,
        toneOf: noticeTone
    }
};

export { cartNoticeLogic };
