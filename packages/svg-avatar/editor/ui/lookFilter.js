import { createRadioGroup } from "./optionGroup.js";

// "Suggestions for: Everyone / Masculine / Feminine" (text options)
const createLookFilter = props => createRadioGroup({ ...props, className: "sa-editor-looks" });

export { createLookFilter };
