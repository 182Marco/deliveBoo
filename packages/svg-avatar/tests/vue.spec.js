import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import { Avatar } from "../vue.js";
import { avatarTraits, renderAvatarSvg } from "../index.js";

const pathsOf = markup => [...new DOMParser().parseFromString(markup, "image/svg+xml").querySelectorAll("path")]
    .map(path => path.getAttribute("d"));

describe("Avatar component", () => {
    it("renders the same paths as renderAvatarSvg for a seed", () => {
        const wrapper = mount(Avatar, { props: { seed: "user-7" } });
        const rendered = wrapper.findAll("path").map(path => path.attributes("d"));
        expect(rendered).toEqual(pathsOf(renderAvatarSvg(avatarTraits("user-7"))));
        expect(wrapper.attributes("width")).toBe("40");
        expect(wrapper.attributes("aria-hidden")).toBe("true");
        expect(wrapper.classes()).toContain("sa-avatar");
    });

    it("applies overrides, explicit traits and the label", () => {
        const withHair = mount(Avatar, { props: { seed: "user-7", overrides: { hair: "long" }, label: "Anna", size: 64 } });
        expect(withHair.attributes("role")).toBe("img");
        expect(withHair.attributes("aria-label")).toBe("Anna");
        expect(withHair.find("title").text()).toBe("Anna");
        expect(withHair.findAll("path").map(p => p.attributes("d")))
            .toEqual(pathsOf(renderAvatarSvg(avatarTraits("user-7", { hair: "long" }))));

        const traits = avatarTraits("someone-else");
        const fixed = mount(Avatar, { props: { seed: "ignored", traits } });
        expect(fixed.findAll("path").map(p => p.attributes("d"))).toEqual(pathsOf(renderAvatarSvg(traits)));
    });

    it("updates when the seed changes and supports the square shape", async () => {
        const wrapper = mount(Avatar, { props: { seed: "a", shape: "square" } });
        expect(wrapper.classes()).toContain("sa-avatar--square");
        const before = wrapper.html();
        await wrapper.setProps({ seed: "b" });
        expect(wrapper.html()).not.toBe(before);
    });
});
