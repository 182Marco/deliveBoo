import { describe, expect, it } from "vitest";
import { readdirSync, readFileSync, statSync } from "node:fs";
import { dirname, join, relative, resolve, sep } from "node:path";
import { fileURLToPath } from "node:url";

// Enforces the package boundary: dependency levels, no imports outside the package,
// agnostic naming, and public-entry-only use from the host app.
const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const APP_SOURCES = [resolve(ROOT, "../../resources/js")];
const PUBLIC_ENTRIES = ["index.js", "vue.js", "editor.js"];
const EXTERNAL_ALLOWED = { vue: 4 };
// host product name, split so this file does not contain it either
const HOST_NAME = ["food", "sy"].join("");

const LEVELS = [
    ["assets/", 0], ["palettes/", 0], ["core/random/", 0], ["core/types.js", 0],
    ["rules/", 1], ["core/traits/", 2], ["core/layers/", 3], ["components/", 4],
    ["editor/state/", 5], ["editor/groups.js", 5], ["editor/labels.js", 5],
    ["editor/editor.css", 5], ["editor/ui/", 6], ["editor/mount.js", 7],
    ["index.js", 8], ["vue.js", 8], ["editor.js", 8]
];

const levelOf = file => LEVELS.find(([prefix]) => file === prefix || file.startsWith(prefix))?.[1];

const walk = dir => readdirSync(dir).flatMap(name => {
    const full = join(dir, name);
    return statSync(full).isDirectory() ? walk(full) : [full];
});

const IMPORT = /(?:import|export)\s+(?:[^"'`;]*?\s+from\s+)?["']([^"']+)["']/g;
const specifiers = source => [...source.matchAll(IMPORT)].map(match => match[1]);

const posix = path => path.split(sep).join("/");
const packageFiles = walk(ROOT).map(full => posix(relative(ROOT, full)));
const codeFiles = packageFiles.filter(file => /\.(js|vue)$/.test(file) && !file.startsWith("tests/"));

describe("package boundaries", () => {
    it("assigns every source file to a dependency level", () => {
        codeFiles.forEach(file => expect(levelOf(file), file).toBeTypeOf("number"));
    });

    it.each(codeFiles)("%s only imports allowed modules", file => {
        const source = readFileSync(join(ROOT, file), "utf8");
        specifiers(source).forEach(spec => {
            if (!spec.startsWith(".")) {
                expect(EXTERNAL_ALLOWED[spec], `${file} imports external "${spec}"`).toBeDefined();
                expect(levelOf(file), `${file} may not import "${spec}"`).toBeGreaterThanOrEqual(EXTERNAL_ALLOWED[spec]);
                return;
            }
            const target = posix(relative(ROOT, resolve(ROOT, dirname(file), spec)));
            expect(target.startsWith(".."), `${file} leaves the package via "${spec}"`).toBe(false);
            if (levelOf(file) < 8) {
                expect(PUBLIC_ENTRIES, `${file} imports a public entry`).not.toContain(target);
            }
            expect(levelOf(target), `${file} -> ${target} goes up a level`).toBeLessThanOrEqual(levelOf(file));
        });
    });

    it("the editor never reaches into the host page (no global DOM lookups)", () => {
        const GLOBAL_LOOKUPS = /document\.(querySelector|querySelectorAll|getElementById|getElementsBy\w+|body|forms)\b/;
        codeFiles.filter(file => file.startsWith("editor")).forEach(file => {
            expect(GLOBAL_LOOKUPS.test(readFileSync(join(ROOT, file), "utf8")), file).toBe(false);
        });
    });

    it("never names the host product", () => {
        packageFiles.forEach(file => {
            expect(readFileSync(join(ROOT, file), "utf8").toLowerCase().includes(HOST_NAME), file).toBe(false);
        });
    });

    it("is used by the app only through its public entries", () => {
        APP_SOURCES.flatMap(walk).filter(file => /\.(js|vue)$/.test(file)).forEach(file => {
            specifiers(readFileSync(file, "utf8")).forEach(spec => {
                expect(spec.includes("packages/svg-avatar"), `${file} imports the package by path`).toBe(false);
                if (spec.startsWith("svg-avatar")) expect(["svg-avatar", "svg-avatar/vue", "svg-avatar/editor"]).toContain(spec);
            });
        });
    });
});
