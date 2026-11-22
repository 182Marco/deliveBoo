// Unit tests of the Vue SPA (npx vitest run). Separate from the asset build.
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue2";

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: "jsdom",
        include: ["tests/js/**/*.spec.js"],
        restoreMocks: true
    }
});
