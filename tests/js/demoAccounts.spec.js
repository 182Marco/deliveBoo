// Login page: "Use test users" and the demo accounts dialog (resources/js/jsBack/demoAccounts.js,
// markup of resources/views/components/demo-accounts.blade.php).
import { describe, it, expect, beforeEach, vi } from "vitest";
import { cardInView, initDemoAccounts, isDemoEmail } from "../../resources/js/jsBack/demoAccounts.js";

const ACCOUNTS = [
    ["admin@foodsy.test", "foodsy-admin"],
    ["restaurant_owner@foodsy.test", "foodsy-owner"],
    ["customer@foodsy.test", "foodsy-demo"]
];

const page = (email = "") => {
    document.body.innerHTML = `
        <form>
            <input id="email" name="email" type="email" value="${email}">
            <input id="password" name="password" type="password">
            <button type="submit" id="logBtn">Log in</button>
            <button type="button" class="auth-demo-trigger" data-demo-open>Use test users</button>
            <dialog id="demoDialog">
                <button type="button" data-demo-close>Close</button>
                <ul class="auth-demo-list">
                    ${ACCOUNTS.map(([e, p]) => `<li><button type="button" class="auth-demo-account" data-demo-email="${e}" data-demo-password="${p}">${e}</button></li>`).join("")}
                </ul>
                <div>${ACCOUNTS.map((_, i) => `<button type="button" class="auth-demo-dot${i ? "" : " auth-demo-dot--active"}" data-demo-dot="${i}"></button>`).join("")}</div>
            </dialog>
        </form>`;
    initDemoAccounts(document);
};

const $ = selector => document.querySelector(selector);
const dialog = () => $("#demoDialog");
const triggerHidden = () => $("[data-demo-open]").classList.contains("auth-demo-trigger--hidden");
const type = value => {
    $("#email").value = value;
    $("#email").dispatchEvent(new Event("input", { bubbles: true }));
};

describe("isDemoEmail", () => {
    it("is a strict string equality", () => {
        const emails = ACCOUNTS.map(([e]) => e);
        expect(isDemoEmail("customer@foodsy.test", emails)).toBe(true);
        expect(isDemoEmail("customer@foodsy.tes", emails)).toBe(false);
        expect(isDemoEmail(" customer@foodsy.test", emails)).toBe(false);
        expect(isDemoEmail("Customer@foodsy.test", emails)).toBe(false);
        expect(isDemoEmail("", emails)).toBe(false);
    });
});

describe("Use test users", () => {
    beforeEach(() => page());

    it("opens the dialog", () => {
        expect(dialog().open).toBe(false);
        $("[data-demo-open]").click();
        expect(dialog().open).toBe(true);
    });

    it("choosing an account fills email and password, closes the dialog and hides the button", () => {
        $("[data-demo-open]").click();
        $('[data-demo-email="restaurant_owner@foodsy.test"]').click();

        expect($("#email").value).toBe("restaurant_owner@foodsy.test");
        expect($("#password").value).toBe("foodsy-owner");
        expect(dialog().open).toBe(false);
        expect(triggerHidden()).toBe(true);
        expect($("[data-demo-open]").getAttribute("aria-hidden")).toBe("true");
        expect(document.activeElement.id).toBe("logBtn");
    });

    it("comes back as soon as the email differs, and hides again on an exact match", () => {
        $("[data-demo-open]").click();
        $('[data-demo-email="customer@foodsy.test"]').click();

        type("customer@foodsy.tes");
        expect(triggerHidden()).toBe(false);
        type("customer@foodsy.test");
        expect(triggerHidden()).toBe(true);
        type("admin@foodsy.test");
        expect(triggerHidden()).toBe(true);
        type("");
        expect(triggerHidden()).toBe(false);
    });

    it("closes with the close button and on a click on the backdrop", () => {
        $("[data-demo-open]").click();
        $("[data-demo-close]").click();
        expect(dialog().open).toBe(false);

        $("[data-demo-open]").click();
        dialog().dispatchEvent(new MouseEvent("click", { bubbles: true }));
        expect(dialog().open).toBe(false);
    });
});

describe("the row of accounts under 900px", () => {
    beforeEach(() => page());

    it("finds the card in view from the scroll position", () => {
        expect(cardInView(0, 300)).toBe(0);
        expect(cardInView(290, 300)).toBe(1);
        expect(cardInView(620, 300)).toBe(2);
        expect(cardInView(120, 0)).toBe(0);
    });

    it("a dot brings its card into view; opening starts again from the first one", () => {
        const items = [...document.querySelectorAll(".auth-demo-list > li")];
        items.forEach(item => (item.scrollIntoView = vi.fn()));
        $('[data-demo-dot="2"]').click();
        expect(items[2].scrollIntoView).toHaveBeenCalledWith({ behavior: "smooth", block: "nearest", inline: "start" });

        $("[data-demo-open]").click();
        const dots = [...document.querySelectorAll("[data-demo-dot]")];
        expect(dots.map(d => d.classList.contains("auth-demo-dot--active"))).toEqual([true, false, false]);
        expect(dots[0].getAttribute("aria-current")).toBe("true");
    });
});

describe("after a failed login", () => {
    it("starts hidden when the email kept by the form is a demo account", () => {
        page("admin@foodsy.test");
        expect(triggerHidden()).toBe(true);
    });

    it("does nothing on pages without the button", () => {
        document.body.innerHTML = "<form><input name='email'></form>";
        expect(initDemoAccounts(document)).toBeNull();
    });
});
