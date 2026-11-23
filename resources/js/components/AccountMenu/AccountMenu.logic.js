import { mapActions, mapState } from "vuex";
import { Avatar } from "svg-avatar/vue";
import { Icon } from "../Icon";
import { t } from "../../utils";
import { accountAreaLink, fullName } from "./AccountMenu.utils.js";

// header account: "Log in" for guests, avatar menu (area of the role, log out) for accounts.
// A native <details>, closed by Esc and by a click outside like the partner hub menu.
const accountMenuLogic = {
    name: "AccountMenu",
    components: { Avatar, Icon },
    data: () => ({
        photoFailed: false
    }),
    computed: {
        ...mapState(["user"]),
        // Google photo when there is one and it loads, otherwise the svg-avatar of the seed
        showPhoto() {
            return !!this.user?.avatar_url && !this.photoFailed;
        },
        fullName() {
            return fullName(this.user);
        },
        areaLink() {
            return accountAreaLink(this.user?.role);
        }
    },
    watch: {
        "user.avatar_url"() {
            this.photoFailed = false;
        }
    },
    mounted() {
        document.addEventListener("click", this.onDocumentClick);
        document.addEventListener("keydown", this.onKeydown);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocumentClick);
        document.removeEventListener("keydown", this.onKeydown);
    },
    methods: {
        ...mapActions(["logoutUser"]),
        t,
        onPhotoError() {
            this.photoFailed = true;
        },
        close() {
            if (this.$refs.menu) this.$refs.menu.open = false;
        },
        onDocumentClick(event) {
            if (this.$refs.menu?.open && !this.$refs.menu.contains(event.target)) this.close();
        },
        onKeydown(event) {
            if (event.key !== "Escape" || !this.$refs.menu?.open) return;
            this.close();
            this.$refs.trigger?.focus();
        },
        async onLogout() {
            this.close();
            await this.logoutUser();
            // a fresh page: new CSRF token and nothing of the account left in memory
            window.location.assign("/");
        }
    }
};

export { accountMenuLogic };
