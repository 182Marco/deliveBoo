// partner area (Blade): the account menu is a native <details>; close it on Esc or on a click outside
const menus = document.querySelectorAll("details.adm-menu");

const closeAll = except => menus.forEach(e => e !== except && e.removeAttribute("open"));

document.addEventListener("click", event => closeAll(event.target.closest("details.adm-menu")));
document.addEventListener("keydown", event => {
    if (event.key === "Escape") closeAll(null);
});
