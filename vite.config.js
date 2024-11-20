import { fileURLToPath, URL } from "node:url";

import process from "node:process";
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
    process.env = { ...process.env, ...loadEnv(mode, process.cwd()) };

    return {
        server: {
            host: process.env.VITE_HOST,
        },
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/main.ts"],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                "vue-i18n": "vue-i18n/dist/vue-i18n.cjs.js",
                "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
            },
        },
        optimizeDeps: {
            esbuildOptions: {
                target: ["es2020", "safari14"],
            },
        },
        build: {
            chunkSizeWarningLimit: 3000,
            target: ["es2020", "safari14"],
            rollupOptions: {
                output: {
                    // expose jQuery as a global variable
                    globals: {
                        jquery: "jQuery",
                    },
                },
            },
        },
    };
});
