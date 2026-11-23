// partner area: jsBack/avatar.js replaces the <x-avatar> initial with the svg-avatar drawing
import { describe, it, expect, beforeEach } from "vitest";
import { avatarTraits, renderAvatarSvg } from "svg-avatar";

// the DOM re-serializes the markup (e.g. <path/> -> <path></path>): compare after the same round trip
const asDom = markup => {
    const box = document.createElement("span");
    box.innerHTML = markup;
    return box.innerHTML;
};

describe("jsBack/avatar.js", () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <span class="adm-avatar" data-avatar-seed="user-3" data-avatar-overrides='{"hair":"bun"}' data-avatar-size="34">M</span>
            <span class="adm-avatar" data-avatar-seed="user-4" data-avatar-overrides="not json" data-avatar-size="24">L</span>`;
    });

    it("draws every placeholder with its seed, overrides and size", async () => {
        const { hydrateAvatars } = await import("../../resources/js/jsBack/avatar.js");
        hydrateAvatars();
        const [first, second] = document.querySelectorAll(".adm-avatar");

        expect(first.innerHTML).toBe(asDom(renderAvatarSvg(avatarTraits("user-3", { hair: "bun" }), { size: "34" })));
        expect(first.classList.contains("adm-avatar--drawn")).toBe(true);
        expect(first.textContent).not.toContain("M");
        // broken overrides fall back to the seed alone
        expect(second.innerHTML).toBe(asDom(renderAvatarSvg(avatarTraits("user-4"), { size: "24" })));
    });
});
