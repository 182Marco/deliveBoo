// partner area (Blade): replaces each <x-avatar> initial with the drawn avatar from the svg-avatar package
import { avatarTraits, renderAvatarSvg } from "svg-avatar";

const parseOverrides = raw => {
    try {
        const value = JSON.parse(raw || "{}");
        return value && typeof value === "object" ? value : {};
    } catch {
        return {};
    }
};

const hydrateAvatar = el => {
    const traits = avatarTraits(el.dataset.avatarSeed, parseOverrides(el.dataset.avatarOverrides));
    el.innerHTML = renderAvatarSvg(traits, { size: el.dataset.avatarSize });
    el.classList.add("adm-avatar--drawn");
};

const hydrateAvatars = (root = document) => root.querySelectorAll("[data-avatar-seed]").forEach(hydrateAvatar);

hydrateAvatars();

export { hydrateAvatars };
