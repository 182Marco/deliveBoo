import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath } from "node:url";

const pkg = file => fileURLToPath(new URL(`./packages/${file}`, import.meta.url));

// same entry points that webpack.mix.js had; output in public/build (manifest read by @vite)
export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/admin.css",
                "resources/js/app.js",
                "resources/js/vue.js",
                "resources/js/jsBack/confDelete.js",
                "resources/js/jsBack/chart.js",
                "resources/js/jsBack/logListen.js"
            ],
            refresh: true
        }),
        vue({
            template: {
                // keep relative src resolved by Vite, absolute URLs (/images/...) untouched
                transformAssetUrls: { base: null, includeAbsolute: false }
            }
        })
    ],
    resolve: {
        // local packages, imported only through their public entry points (no deep imports)
        alias: [
            { find: /^svg-avatar$/, replacement: pkg("svg-avatar/index.js") },
            { find: /^svg-avatar\/vue$/, replacement: pkg("svg-avatar/vue.js") },
            { find: /^svg-avatar\/editor$/, replacement: pkg("svg-avatar/editor.js") }
        ]
    },
    test: {
        environment: "jsdom",
        include: ["tests/js/**/*.spec.js", "packages/*/tests/**/*.spec.js"],
        restoreMocks: true
    }
});
