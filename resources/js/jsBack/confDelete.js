// confirmation dialog (layouts/deleteConfirm.blade.php) before every ".delete-post-form" submit
const confirmBox = document.querySelector("#deleteConfirm");
const btnConfirm = document.querySelector("#deleteConfermed");
const btnAbort = document.querySelector("#deleteAbort");

// form waiting for the answer
let pendingForm = null;

const showBox = () => {
    confirmBox.hidden = false;
    btnAbort?.focus();
};
const hideBox = () => {
    confirmBox.hidden = true;
};

document.querySelectorAll(".delete-post-form").forEach(e =>
    e.addEventListener("submit", event => {
        event.preventDefault();
        pendingForm = event.currentTarget;
        showBox();
    })
);

btnConfirm?.addEventListener("click", () => {
    pendingForm?.submit();
    hideBox();
});

btnAbort?.addEventListener("click", () => {
    pendingForm = null;
    hideBox();
});

// Esc or a click on the backdrop keeps the item
confirmBox?.addEventListener("keydown", event => {
    if (event.key === "Escape") btnAbort.click();
});
confirmBox?.addEventListener("click", event => {
    if (event.target === confirmBox) btnAbort.click();
});
