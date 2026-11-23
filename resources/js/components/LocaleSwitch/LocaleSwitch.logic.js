import { nextTick } from "vue";
import { Flag } from "../Flag";
import { Icon } from "../Icon";
import { currentLocale, LOCALES, setLocale, t } from "../../utils";

// language menu of the header: flag + short code, the whole site switches at once (no reload)
const localeSwitchLogic = {
    name: "LocaleSwitch",
    components: { Flag, Icon },
    data: () => ({
        open: false,
        locales: LOCALES
    }),
    computed: {
        current() {
            return LOCALES.find(e => e.code === currentLocale()) ?? LOCALES[0];
        }
    },
    created() {
        // not reactive on purpose: a listener is not view state
        this.onPointerDown = event => {
            if (!this.$el.contains(event.target)) this.close(false);
        };
    },
    beforeUnmount() {
        document.removeEventListener("pointerdown", this.onPointerDown);
    },
    methods: {
        t,
        // keyboard users land on the current language, so the arrows and Enter work at once
        async toggle() {
            if (this.open) {
                this.close(false);
                return;
            }
            this.open = true;
            document.addEventListener("pointerdown", this.onPointerDown);
            await nextTick();
            this.options().find(e => e.getAttribute("aria-current"))?.focus();
        },
        close(restoreFocus) {
            if (!this.open) return;
            this.open = false;
            document.removeEventListener("pointerdown", this.onPointerDown);
            if (restoreFocus) nextTick(() => this.$refs.toggle?.focus());
        },
        choose(code) {
            setLocale(code);
            this.close(true);
        },
        options() {
            return this.$refs.options ?? [];
        },
        // up/down move between the languages, wrapping around
        move(step) {
            const options = this.options();
            const index = options.indexOf(document.activeElement);
            options[(index + step + options.length) % options.length]?.focus();
        },
        // Tab out of the menu closes it
        onFocusOut(event) {
            if (this.open && !this.$el.contains(event.relatedTarget)) this.close(false);
        }
    }
};

export { localeSwitchLogic };
