// confirmation box (layouts/deleteConfirm.blade.php) before every ".delete-post-form" submit
const confirmBox = document.querySelector(".del-Container");
const btnConfirm = document.querySelector("#deleteConfermed");
const btnAbort = document.querySelector("#deleteAbort");

// form waiting for the answer
let pendingForm = null;

const showBox = () => confirmBox.classList.remove("no-show");
const hideBox = () => confirmBox.classList.add("no-show");

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
