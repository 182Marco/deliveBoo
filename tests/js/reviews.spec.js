// Reviews on the site: star helpers, the star picker, the review dialog, "My orders" and the
// reviews of the restaurant page. axios is mocked: no request leaves the test.
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount, flushPromises, RouterLinkStub } from "@vue/test-utils";
import axios from "axios";
import { MyOrders } from "../../resources/js/pages";
import { RestReviews, ReviewModal, StarInput, StarsBox } from "../../resources/js/components";
import { starStates } from "../../resources/js/components/StarsBox/StarsBox.utils.js";
import { nextValue, tabIndexFor } from "../../resources/js/components/StarInput/StarInput.utils.js";
import { saveError, trapTarget } from "../../resources/js/components/ReviewModal/ReviewModal.utils.js";
import { distributionRows } from "../../resources/js/components/RestReviews/RestReviews.utils.js";
import { reviewAction, toOrderView, withReview } from "../../resources/js/pages/MyOrders/MyOrders.utils.js";
import { formatRating, t } from "../../resources/js/utils";
import { createAppStore as createStore } from "../../resources/js/store.js";

vi.mock("axios", () => ({ default: { get: vi.fn(), post: vi.fn(), put: vi.fn() } }));

const ORDER = {
    id: 42,
    placed_at: "2026-11-20T19:30:00+01:00",
    delivered_at: "2026-11-20T20:05:00+01:00",
    price: 12.5,
    status: "paid",
    fulfillment_status: "delivered",
    address: "Langstrasse 1",
    restaurant: { name: "Sushi Kreis 4", slug: "sushi-kreis-4", img: null },
    lines: [{ name: "Maki", quantity: 2, unit_price: 4 }],
    review: null,
    can_review: true
};
const REVIEW = { rating: 4, comment: "Hot and quick.", reply: null, replied_at: null, hidden: false, created_at: "2026-11-21T10:00:00+01:00" };
const PAGE = data => ({ data, current_page: 1, last_page: 1, total: data.length });

let wrappers = [];
const keep = wrapper => {
    wrappers.push(wrapper);
    return wrapper;
};

beforeEach(() => {
    localStorage.clear();
    axios.get.mockReset();
    axios.put.mockReset();
    window.scrollTo = vi.fn();
    Element.prototype.scrollIntoView = vi.fn();
    vi.spyOn(console, "error").mockImplementation(() => {});
});
afterEach(() => {
    wrappers.forEach(e => e.unmount());
    wrappers = [];
    vi.restoreAllMocks();
});

describe("star helpers", () => {
    it("rounds to half a star, never up to the next whole one", () => {
        expect(starStates(4.74)).toEqual(["full", "full", "full", "full", "half"]);
        expect(starStates(4.76)).toEqual(["full", "full", "full", "full", "full"]);
        expect(starStates(3.1)).toEqual(["full", "full", "full", "empty", "empty"]);
        expect(starStates(null)).toEqual(["empty", "empty", "empty", "empty", "empty"]);
        expect(formatRating(4.567)).toBe("4.6");
        expect(formatRating(null)).toBe("0.0");
    });

    it("StarsBox shows the number and the count only when asked", () => {
        const plain = keep(mount(StarsBox, { props: { average: 3.5 } }));
        expect(plain.findAll(".ui-star--on")).toHaveLength(4);
        expect(plain.find(".ui-star-half").exists()).toBe(true);
        expect(plain.find(".stars-meta").exists()).toBe(false);

        const counted = keep(mount(StarsBox, { props: { average: 4.56, count: 12 } }));
        expect(counted.find(".stars-meta").text()).toContain("4.6");
        expect(counted.find(".stars-meta").text()).toContain("12");
    });

    it("keyboard of the star picker", () => {
        expect(nextValue("ArrowRight", 0)).toBe(1);
        expect(nextValue("ArrowRight", 5)).toBe(5);
        expect(nextValue("ArrowLeft", 1)).toBe(1);
        expect(nextValue("ArrowDown", 4)).toBe(3);
        expect(nextValue("Home", 4)).toBe(1);
        expect(nextValue("End", 2)).toBe(5);
        expect(nextValue("Enter", 2)).toBeNull();
        expect(tabIndexFor(1, 0)).toBe(0);
        expect(tabIndexFor(2, 0)).toBe(-1);
        expect(tabIndexFor(3, 3)).toBe(0);
    });
});

describe("StarInput", () => {
    it("fills the stars up to the one clicked and works with the keyboard", async () => {
        const wrapper = keep(mount(StarInput, { props: { modelValue: 0, "onUpdate:modelValue": v => wrapper.setProps({ modelValue: v }) }, attachTo: document.body }));
        const stars = wrapper.findAll("[role='radio']");
        expect(stars).toHaveLength(5);
        expect(wrapper.findAll(".star-input-btn--on")).toHaveLength(0);

        await stars[2].trigger("click");
        expect(wrapper.emitted("update:modelValue")[0]).toEqual([3]);
        expect(wrapper.findAll(".star-input-btn--on")).toHaveLength(3);
        expect(stars[2].attributes("aria-checked")).toBe("true");
        expect(stars[2].attributes("tabindex")).toBe("0");
        expect(stars[0].attributes("tabindex")).toBe("-1");

        // the pointer previews, leaving the group shows the choice again
        await stars[4].trigger("mouseenter");
        expect(wrapper.findAll(".star-input-btn--on")).toHaveLength(5);
        await wrapper.find("[role='radiogroup']").trigger("mouseleave");
        expect(wrapper.findAll(".star-input-btn--on")).toHaveLength(3);

        await stars[2].trigger("keydown", { key: "ArrowRight" });
        expect(wrapper.emitted("update:modelValue").at(-1)).toEqual([4]);
        expect(document.activeElement).toBe(stars[3].element);
        expect(stars[3].attributes("aria-label")).toBe(`${t("reviews.star", { count: 4 })}, ${t("reviews.level.4")}`);
    });
});

describe("ReviewModal", () => {
    it("asks for the stars before saving", async () => {
        const wrapper = keep(mount(ReviewModal, { props: { orderId: 42, restaurant: "Sushi" }, attachTo: document.body }));
        await wrapper.find("form").trigger("submit");
        expect(wrapper.find("[role='alert']").text()).toBe(t("reviews.chooseStars"));
        expect(axios.put).not.toHaveBeenCalled();
    });

    it("saves stars and text and hands the answer to the page", async () => {
        axios.put.mockResolvedValue({ data: { review: REVIEW, can_review: true } });
        const wrapper = keep(mount(ReviewModal, { props: { orderId: 42, restaurant: "Sushi" }, attachTo: document.body }));
        await wrapper.findAll("[role='radio']")[3].trigger("click");
        await wrapper.find("textarea").setValue("  Hot and quick.  ");
        expect(wrapper.find(".review-counter").text()).toContain("/1000");
        await wrapper.find("form").trigger("submit");
        await flushPromises();

        expect(axios.put).toHaveBeenCalledWith("/api/me/orders/42/review", { rating: 4, comment: "Hot and quick." });
        expect(wrapper.emitted("saved")[0]).toEqual([{ review: REVIEW, can_review: true }]);
    });

    it("an empty text is no text; editing starts from the saved review", async () => {
        axios.put.mockResolvedValue({ data: { review: { ...REVIEW, comment: null }, can_review: true } });
        const wrapper = keep(mount(ReviewModal, { props: { orderId: 42, review: REVIEW }, attachTo: document.body }));
        expect(wrapper.find("textarea").element.value).toBe("Hot and quick.");
        expect(wrapper.find("[aria-checked='true']").attributes("aria-label")).toContain(t("reviews.level.4"));
        expect(wrapper.find("button[type='submit']").text()).toBe(t("reviews.save"));
        await wrapper.find("textarea").setValue("   ");
        await wrapper.find("form").trigger("submit");
        await flushPromises();
        expect(axios.put).toHaveBeenCalledWith("/api/me/orders/42/review", { rating: 4, comment: null });
    });

    it("shows the server errors and closes with Esc or Cancel", async () => {
        axios.put.mockRejectedValue({ response: { status: 403 } });
        const wrapper = keep(mount(ReviewModal, { props: { orderId: 42, review: REVIEW }, attachTo: document.body }));
        await wrapper.find("form").trigger("submit");
        await flushPromises();
        expect(wrapper.find("[role='alert']").text()).toBe(t("reviews.forbidden"));

        await wrapper.find(".ui-modal-backdrop").trigger("keydown", { key: "Escape" });
        await wrapper.find(".ui-btn--outline").trigger("click");
        expect(wrapper.emitted("close")).toHaveLength(2);
    });

    it("error and focus helpers", () => {
        expect(saveError({ response: { status: 422, data: { errors: { comment: ["Too long."] } } } }, t)).toBe("Too long.");
        expect(saveError({ response: { status: 500 } }, t)).toBe(t("reviews.error"));
        expect(saveError(new Error("offline"), t)).toBe(t("reviews.error"));
        const [a, b, c] = ["a", "b", "c"];
        expect(trapTarget([a, b, c], c, false)).toBe(a);
        expect(trapTarget([a, b, c], a, true)).toBe(c);
        expect(trapTarget([a, b, c], b, false)).toBeNull();
        expect(trapTarget([a, b, c], "outside", false)).toBe(a);
    });
});

describe("My orders", () => {
    it("the button writes, edits or is not there", () => {
        expect(reviewAction({ can_review: true, review: null })).toBe("write");
        expect(reviewAction({ can_review: true, review: REVIEW })).toBe("edit");
        expect(reviewAction({ can_review: false, review: REVIEW })).toBe("none");
        expect(reviewAction({ can_review: false, review: null })).toBe("none");
        expect(toOrderView(ORDER, "en").action).toBe("write");
        expect(withReview(ORDER, { review: REVIEW, can_review: false })).toMatchObject({ id: 42, review: REVIEW, can_review: false });
    });

    it("opens the dialog next to Order again and shows the review once saved", async () => {
        axios.get.mockResolvedValue({ data: PAGE([ORDER, { ...ORDER, id: 43, can_review: false }]) });
        axios.put.mockResolvedValue({ data: { review: { ...REVIEW, reply: null }, can_review: true } });
        const wrapper = keep(mount(MyOrders, { global: { plugins: [createStore()], stubs: { RouterLink: RouterLinkStub } }, attachTo: document.body }));
        await flushPromises();

        const buttons = wrapper.findAll(".orders-actions button");
        expect(buttons).toHaveLength(1);
        expect(buttons[0].text()).toBe(t("reviews.review"));

        await buttons[0].trigger("click");
        expect(wrapper.findComponent(ReviewModal).exists()).toBe(true);
        await wrapper.findAll("[role='radio']")[3].trigger("click");
        await wrapper.find("form").trigger("submit");
        await flushPromises();

        expect(wrapper.findComponent(ReviewModal).exists()).toBe(false);
        expect(wrapper.find(".orders-review-text").text()).toBe("Hot and quick.");
        expect(wrapper.find(".orders-actions button").text()).toBe(t("reviews.edit"));
        expect(wrapper.find(".orders-notice").text()).toBe(t("reviews.saved"));
    });

    it("a hidden review says so and has no button; the reply of the restaurant is shown", async () => {
        axios.get.mockResolvedValue({
            data: PAGE([
                { ...ORDER, review: { ...REVIEW, hidden: true }, can_review: false },
                { ...ORDER, id: 43, review: { ...REVIEW, reply: "Thank you!" } }
            ])
        });
        const wrapper = keep(mount(MyOrders, { global: { plugins: [createStore()], stubs: { RouterLink: RouterLinkStub } } }));
        await flushPromises();

        expect(wrapper.find(".orders-review-hidden").text()).toBe(t("reviews.hiddenNote"));
        expect(wrapper.find(".orders-reply-text").text()).toBe("Thank you!");
        expect(wrapper.findAll(".orders-actions button")).toHaveLength(1);
    });
});

describe("reviews of the restaurant page", () => {
    const ANSWER = {
        summary: { average: 4.5, count: 2, distribution: { 5: 1, 4: 1, 3: 0, 2: 0, 1: 0 } },
        reviews: {
            data: [
                { id: 1, rating: 5, comment: "Great food.", created_at: "2026-11-20T21:00:00+01:00", author: { name: "Anna B.", avatar_seed: "user-1", avatar_overrides: {} }, reply: "Thanks!", replied_at: null },
                { id: 2, rating: 4, comment: "Good.", created_at: "2026-11-19T21:00:00+01:00", author: null, reply: null, replied_at: null }
            ],
            current_page: 1,
            last_page: 1
        }
    };

    it("distribution bars relative to the longest one", () => {
        expect(distributionRows({ 5: 4, 4: 2 })).toEqual([
            { stars: 5, count: 4, share: 1 },
            { stars: 4, count: 2, share: 0.5 },
            { stars: 3, count: 0, share: 0 },
            { stars: 2, count: 0, share: 0 },
            { stars: 1, count: 0, share: 0 }
        ]);
    });

    it("summary, reviews with the reply, anonymous authors", async () => {
        axios.get.mockResolvedValue({ data: ANSWER });
        const wrapper = keep(mount(RestReviews, { props: { slug: "sushi", restaurant: "Sushi" } }));
        await flushPromises();

        expect(axios.get).toHaveBeenCalledWith("/api/restaurants/sushi/reviews", { params: { page: 1 } });
        expect(wrapper.find(".reviews-average").text()).toBe("4.5");
        expect(wrapper.findAll(".reviews-card")).toHaveLength(2);
        expect(wrapper.find(".reviews-reply-text").text()).toBe("Thanks!");
        expect(wrapper.findAll(".reviews-name")[1].text()).toBe(t("reviews.anonymous"));
    });

    it("empty and error states", async () => {
        axios.get.mockResolvedValueOnce({ data: { summary: { average: null, count: 0, distribution: {} }, reviews: { data: [], current_page: 1, last_page: 1 } } });
        const empty = keep(mount(RestReviews, { props: { slug: "new-place" } }));
        await flushPromises();
        expect(empty.find(".reviews-empty").exists()).toBe(true);

        axios.get.mockRejectedValueOnce(new Error("offline"));
        const failed = keep(mount(RestReviews, { props: { slug: "x" } }));
        await flushPromises();
        expect(failed.find("[role='alert']").text()).toContain(t("reviews.loadError"));
    });
});
