// all delete btns
const delForms = document.querySelectorAll(".delete-post-form");
// del confim container
const confirmBox = document.querySelector(".del-Container");
// btn confirm
const btnConf = document.querySelector("#deleteConfermed");
// btn quit
const btnQuit = document.querySelector("#deleteAbort");

delForms.forEach(btn =>
    btn.addEventListener("submit", function(e) {
        e.preventDefault();
        // show confirm box
        show();
        const thisForm = this;
        // if click confirm -> submit + no-show confirm box
        btnConf.addEventListener("click", function() {
            thisForm.submit();
            noShow();
        });
        // if click no Confirm ->  no-show confirm
        btnQuit.addEventListener("click", function() {
            noShow();
        });
    })
);

// FUNCTION UTILITIES
// show confirm box
function show() {
    confirmBox.classList.remove("no-show");
}
// unshow confirm box
function noShow() {
    confirmBox.classList.add("no-show");
}
