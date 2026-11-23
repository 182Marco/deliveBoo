// pure helpers of AccountMenu

const AREA_LINKS = {
    restaurateur: { icon: "store", text: "account.partnerHub" },
    admin: { icon: "chart", text: "account.platform" }
};

// partners and admins get a link to their area; customers already are at home on the site
const accountAreaLink = role => AREA_LINKS[role] ?? null;

const fullName = user => (user ? [user.name, user.last_name].filter(Boolean).join(" ") : "");

export { accountAreaLink, fullName };
