import { createRadioGroup } from "./optionGroup.js";

// colour choices: the same radio group, with round swatches
const createSwatchGroup = props => createRadioGroup({ ...props, className: "sa-editor-swatches" });

export { createSwatchGroup };
