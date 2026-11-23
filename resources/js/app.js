// partner area (Blade): the account menu and the language menu are native <details>;
// close them on Esc or on a click outside
const menus = document.querySelectorAll("details.adm-menu, details.ui-lang");

const closeAll = except => menus.forEach(e => e !== except && e.removeAttribute("open"));

document.addEventListener("click", event => closeAll(event.target.closest("details.adm-menu, details.ui-lang")));
document.addEventListener("keydown", event => {
    if (event.key === "Escape") closeAll(null);
});

// the language trigger and its chevron follow the open state (one class per selector, no [open] descendants)
document.querySelectorAll("details.ui-lang").forEach(menu =>
    menu.addEventListener("toggle", () => {
        menu.querySelector(".ui-lang-trigger")?.classList.toggle("ui-lang-trigger--open", menu.open);
        menu.querySelector(".ui-lang-chevron")?.classList.toggle("ui-lang-chevron--open", menu.open);
    })
);
