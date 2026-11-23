// Login page: "Use test users" and the demo accounts dialog (resources/js/jsBack/demoAccounts.js,
// markup of resources/views/components/demo-accounts.blade.php).
import { describe, it, expect, beforeEach } from "vitest";
import { initDemoAccounts, isDemoEmail } from "../../resources/js/jsBack/demoAccounts.js";

const ACCOUNTS = [
    ["admin@foodsy.test", "foodsy-admin"],
    ["restaurant_owner@foodsy.test", "12345678"],
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
                ${ACCOUNTS.map(([e, p]) => `<button type="button" class="auth-demo-account" data-demo-email="${e}" data-demo-password="${p}">${e}</button>`).join("")}
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
        expect($("#password").value).toBe("12345678");
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
