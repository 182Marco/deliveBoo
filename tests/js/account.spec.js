// Account of the Vue site (dev/PIANO_AUTH_CLIENTI.md, step 2.3): /api/me in the store, never in
// localStorage, and the header AccountMenu (login link, avatar menu, area link, logout).
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import axios from "axios";
import { createAppStore } from "../../resources/js/store.js";
import { AccountMenu } from "../../resources/js/components";
import { accountAreaLink, fullName } from "../../resources/js/components/AccountMenu/AccountMenu.utils.js";

vi.mock("axios", () => ({ default: { get: vi.fn(), post: vi.fn() } }));

const MARIO = {
    id: 62, name: "Mario", last_name: "Rossi", email: "mario.rossi@foodsy.test", phone: null,
    role: "customer", home: "/", avatar_url: null, avatar_seed: "user-62", avatar_overrides: {}
};

let store;
let wrapper;

beforeEach(() => {
    localStorage.clear();
    axios.get.mockReset();
    axios.post.mockReset();
    store = createAppStore();
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    vi.unstubAllGlobals();
});

const mountMenu = () => {
    wrapper = mount(AccountMenu, { global: { plugins: [store] }, attachTo: document.body });
    return wrapper;
};

describe("store", () => {
    it("loads the account from /api/me", async () => {
        axios.get.mockResolvedValue({ data: { user: MARIO } });
        await store.dispatch("loadUser");
        expect(axios.get).toHaveBeenCalledWith("/api/me");
        expect(store.state.user).toEqual(MARIO);
    });

    it("treats an error (banned account) as a guest", async () => {
        store.commit("setUser", MARIO);
        axios.get.mockRejectedValue({ response: { status: 403 } });
        await store.dispatch("loadUser");
        expect(store.state.user).toBeNull();
    });

    it("never writes the account in localStorage", async () => {
        store.commit("setUser", MARIO);
        store.commit("addType", 3);
        await flushPromises();
        const saved = JSON.parse(localStorage.getItem("vuex"));
        expect(saved.typesSelected).toEqual([3]);
        expect(saved).not.toHaveProperty("user");
        expect(localStorage.getItem("vuex")).not.toContain("mario.rossi");
    });

    it("logs out on the server and forgets the account", async () => {
        store.commit("setUser", MARIO);
        axios.post.mockResolvedValue({ status: 204 });
        await store.dispatch("logoutUser");
        expect(axios.post).toHaveBeenCalledWith("/logout");
        expect(store.state.user).toBeNull();
    });
});

describe("AccountMenu", () => {
    it("shows the login link to guests", () => {
        const link = mountMenu().find("a.account-login");
        expect(link.attributes("href")).toBe("/login");
        expect(wrapper.find("details").exists()).toBe(false);
    });

    it("shows the svg avatar, the name and no area link to a customer", () => {
        store.commit("setUser", MARIO);
        mountMenu();
        expect(wrapper.find("a.account-login").exists()).toBe(false);
        expect(wrapper.find("summary svg").exists()).toBe(true);
        expect(wrapper.find(".account-name").text()).toBe("Mario");
        expect(wrapper.find(".account-full").text()).toBe("Mario Rossi");
        expect(wrapper.find("a.account-item").exists()).toBe(false);
    });

    it("links partners and admins to their area", () => {
        store.commit("setUser", { ...MARIO, role: "restaurateur", home: "/admin" });
        mountMenu();
        expect(wrapper.find("a.account-item").attributes("href")).toBe("/admin");
    });

    it("uses the Google photo and falls back to the avatar when it does not load", async () => {
        store.commit("setUser", { ...MARIO, avatar_url: "https://lh3.example.com/photo.jpg" });
        mountMenu();
        const img = wrapper.find("img.account-photo");
        expect(img.attributes("src")).toBe("https://lh3.example.com/photo.jpg");
        await img.trigger("error");
        expect(wrapper.find("img.account-photo").exists()).toBe(false);
        expect(wrapper.find("summary svg").exists()).toBe(true);
    });

    it("closes with Esc and with a click outside", async () => {
        store.commit("setUser", MARIO);
        mountMenu();
        const details = wrapper.find("details").element;
        details.open = true;
        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
        expect(details.open).toBe(false);
        details.open = true;
        document.body.dispatchEvent(new MouseEvent("click", { bubbles: true }));
        expect(details.open).toBe(false);
    });

    it("logs out and reloads the home page", async () => {
        const assign = vi.fn();
        vi.stubGlobal("location", { ...window.location, assign });
        store.commit("setUser", MARIO);
        axios.post.mockResolvedValue({ status: 204 });
        mountMenu();
        await wrapper.find("button.account-item").trigger("click");
        await flushPromises();
        expect(axios.post).toHaveBeenCalledWith("/logout");
        expect(store.state.user).toBeNull();
        expect(assign).toHaveBeenCalledWith("/");
    });
});

describe("utils", () => {
    it("area links and full name", () => {
        expect(accountAreaLink("customer")).toBeNull();
        expect(accountAreaLink("restaurateur").text).toBe("account.partnerHub");
        expect(accountAreaLink("admin").text).toBe("account.platform");
        expect(fullName(MARIO)).toBe("Mario Rossi");
        expect(fullName(null)).toBe("");
    });
});
