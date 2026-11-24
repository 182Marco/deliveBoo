import { t } from "../../utils";

// errors in red, the rest as information (codes from the server merge or from the store)
const ERRORS = ["error", "server", "offline"];

const noticeTone = notice => (ERRORS.includes(notice.code) ? "ui-notice--error" : "ui-notice--info");

// "server": the message comes already translated by the API
const noticeText = notice => (notice.code === "server" ? notice.params.message : t(`cartNotice.${notice.code}`, notice.params));

export { noticeText, noticeTone };
