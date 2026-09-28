import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import * as Vue from 'vue';
import { statamicExternals } from './build/statamic-externals.js';

export default defineConfig({
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        hmr: {
            protocol: 'ws',
            host: 'localhost',
        },
        cors: {
            origin: ['https://statamic-addons.ddev.site'],
            credentials: true,
        },
    },
    plugins: [
        statamicExternals(Vue),
        laravel({
            input: [
                'resources/js/addon.js',
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
    build: {
        outDir: 'resources/dist',
        emptyOutDir: false,
        manifest: false,
        lib: {
            entry: 'resources/js/addon.js',
            name: 'BunnyStreamV6',
            formats: ['iife'],
            fileName: () => 'addon-v6.js',
        },
        rollupOptions: {
            output: {
                assetFileNames: 'addon.[ext]',
            },
        },
    },
});
