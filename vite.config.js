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
                'resources/css/addon.css',
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
    build: {
        outDir: 'resources/dist',
        emptyOutDir: false,
        manifest: false,
        rollupOptions: {
            output: {
                entryFileNames: (chunk) => chunk.name === 'addon' ? 'addon-v6.js' : '[name]-[hash].js',
                chunkFileNames: '[name]-[hash].js',
                assetFileNames: (asset) => asset.name === 'addon.css' ? 'addon.css' : '[name]-[hash][extname]',
            },
        },
    },
});
