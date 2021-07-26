// GET REFERENCES ONLY IF PRESENT IN DOCUMENT
if (document.getElementById("logBtn") !== null) {
    const logBtn = document.getElementById("logBtn");

    logBtn.addEventListener("click", () =>
        localStorage.setItem("clickedLog", true)
    );
}

if (localStorage.getItem("clickedLog")) {
    //  search or message reference only if present in page
    if (document.getElementById("loggedMsgEl") !== null) {
        document.getElementById("loggedMsgEl").classList.remove("no-show");
        // remove local storage -> if users comes in view restaurant.index
        // after he's done something with his restaurant he should not se message logged
        localStorage.removeItem("clickedLog");
    }
}
