// one icon set for the Vue site and the Blade partner area (resources/views/components/icon.blade.php)
import ICONS from "../../../icons/icons.json";

const iconPath = name => ICONS[name] ?? "";

export { iconPath };
