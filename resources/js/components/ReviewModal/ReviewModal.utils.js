// pure helpers of the review dialog

const COMMENT_MAX = 1000;

// elements reachable with Tab inside the dialog (focus stays in it while it is open)
const FOCUSABLE = "button:not([disabled]), textarea:not([disabled]), input:not([disabled]), a[href], [tabindex='0']";

// the element Tab should move to: wraps from the last to the first and back (null = let the browser do it)
const trapTarget = (elements, active, backwards) => {
    if (!elements.length) return null;
    const first = elements[0];
    const last = elements[elements.length - 1];
    if (backwards && active === first) return last;
    if (!backwards && active === last) return first;
    return elements.includes(active) ? null : first;
};

// the message to show for a failed save: the first validation error, "not allowed" or a generic one
const saveError = (error, t) => {
    const status = error?.response?.status;
    const errors = error?.response?.data?.errors;
    if (status === 422 && errors) return Object.values(errors).flat()[0] ?? t("reviews.error");
    return status === 403 ? t("reviews.forbidden") : t("reviews.error");
};

export { COMMENT_MAX, FOCUSABLE, saveError, trapTarget };
