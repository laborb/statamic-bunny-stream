import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue2';
import * as compiler from 'vue/compiler-sfc';

const root = fileURLToPath(new URL('../..', import.meta.url));

export default defineConfig({
    root,
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('../../resources/js', import.meta.url)),
        },
    },
    plugins: [vue({ compiler })],
    build: {
        outDir: fileURLToPath(new URL('../../resources/dist', import.meta.url)),
        emptyOutDir: false,
        lib: {
            entry: fileURLToPath(new URL('../../resources/js/addon-v5.js', import.meta.url)),
            name: 'BunnyStreamV5',
            formats: ['iife'],
            fileName: () => 'addon-v5.js',
        },
    },
});
