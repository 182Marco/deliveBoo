// Login page: "Use test users" opens a dialog with the demo accounts (components/demo-accounts.blade.php).
// Choosing one fills email and password and closes the dialog. While the email field holds exactly
// one of the demo emails the button is hidden (it keeps its room: the page never moves); as soon as
// the value differs, it is back.

const HIDDEN = "auth-demo-trigger--hidden";

// strict equality with one of the demo emails (no trimming, no case folding)
const isDemoEmail = (value, emails) => emails.includes(value);

// the native dialog when the browser has it (focus trap, Esc, inert page), otherwise the open attribute
const openDialog = dialog => (typeof dialog.showModal === "function" ? dialog.showModal() : dialog.setAttribute("open", ""));
const closeDialog = dialog => (typeof dialog.close === "function" ? dialog.close() : dialog.removeAttribute("open"));

// the form reacts as if the person had typed: same events as typing
const fill = (input, value) => {
    input.value = value;
    input.dispatchEvent(new Event("input", { bubbles: true }));
    input.dispatchEvent(new Event("change", { bubbles: true }));
};

const initDemoAccounts = root => {
    const trigger = root.querySelector("[data-demo-open]");
    const dialog = root.getElementById ? root.getElementById("demoDialog") : root.querySelector("#demoDialog");
    const form = trigger?.closest("form");
    const email = form?.querySelector('input[name="email"]');
    const password = form?.querySelector('input[name="password"]');
    if (!trigger || !dialog || !email || !password) return null;

    const accounts = [...dialog.querySelectorAll("[data-demo-email]")];
    const emails = accounts.map(e => e.dataset.demoEmail);

    const sync = () => {
        const hidden = isDemoEmail(email.value, emails);
        trigger.classList.toggle(HIDDEN, hidden);
        // hidden by visibility: out of the tab order and of the accessibility tree too
        trigger.setAttribute("aria-hidden", String(hidden));
    };

    trigger.addEventListener("click", () => openDialog(dialog));
    dialog.querySelector("[data-demo-close]")?.addEventListener("click", () => closeDialog(dialog));
    // a click on the backdrop (outside the panel) closes it
    dialog.addEventListener("click", event => {
        if (event.target === dialog) closeDialog(dialog);
    });
    // back to the button that opened it, unless an account was chosen (then focus goes to "Log in")
    dialog.addEventListener("close", () => {
        if (!isDemoEmail(email.value, emails)) trigger.focus();
    });

    accounts.forEach(account =>
        account.addEventListener("click", () => {
            fill(email, account.dataset.demoEmail);
            fill(password, account.dataset.demoPassword);
            closeDialog(dialog);
            form.querySelector('[type="submit"]')?.focus();
        })
    );

    email.addEventListener("input", sync);
    // old('email') after a failed login may already be a demo account
    sync();

    return { sync };
};

initDemoAccounts(document);

export { initDemoAccounts, isDemoEmail };
