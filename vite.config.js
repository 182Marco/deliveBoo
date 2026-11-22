import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

// same entry points that webpack.mix.js had; output in public/build (manifest read by @vite)
export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/sass/app.scss",
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
                // keep relative src like "../img/logo.png" resolved by Vite, absolute URLs untouched
                transformAssetUrls: { base: null, includeAbsolute: false }
            }
        })
    ],
    css: {
        preprocessorOptions: {
            // Bootstrap 4 SCSS uses syntax deprecated by Dart Sass: silence the noise
            scss: { quietDeps: true, silenceDeprecations: ["import", "global-builtin", "color-functions", "slash-div"] }
        }
    },
    test: {
        environment: "jsdom",
        include: ["tests/js/**/*.spec.js"],
        restoreMocks: true
    }
});
