import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

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
    test: {
        environment: "jsdom",
        include: ["tests/js/**/*.spec.js"],
        restoreMocks: true
    }
});
