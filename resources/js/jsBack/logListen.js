// "logged in" message: shown once, on the first page after clicking the login button
const LOG_KEY = "clickedLog";

const logBtn = document.getElementById("logBtn");
const loggedMsg = document.getElementById("loggedMsgEl");

logBtn?.addEventListener("click", () => localStorage.setItem(LOG_KEY, true));

// removed once shown: coming back to restaurant.index later must not show it again
if (localStorage.getItem(LOG_KEY) && loggedMsg) {
    loggedMsg.hidden = false;
    localStorage.removeItem(LOG_KEY);
}
