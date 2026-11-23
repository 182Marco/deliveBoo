// one set of flags for the Vue site and the Blade partner area (resources/views/components/flag.blade.php):
// a few filled or stroked paths on a 30×20 canvas, a few hundred bytes in all
import FLAGS from "../../../icons/flags.json";

const flagShapes = code => FLAGS[code] ?? [];

// flags are 3:2
const flagHeight = width => Math.round((width * 2) / 3);

export { flagHeight, flagShapes };
