import { describe, expect, it } from "vitest";
import { splitRows } from "../../resources/js/components/TypeSelection/TypeSelection.logic.js";

const types = (n) => Array.from({ length: n }, (_, id) => ({ id }));

describe("splitRows", () => {
    it("splits the cuisines in consecutive rows that keep the reading order", () => {
        const rows = splitRows(types(41));
        expect(rows).toHaveLength(3);
        expect(rows.flat().map((t) => t.id)).toEqual(types(41).map((t) => t.id));
    });

    it("never makes empty rows", () => {
        expect(splitRows(types(2))).toHaveLength(2);
        expect(splitRows([])).toEqual([]);
    });
});
