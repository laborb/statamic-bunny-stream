import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import * as Vue from 'vue';

function statamicExternals() {
    const vueModule = '\0vue-external';
    const vueExports = Object.keys(Vue).filter((key) => key !== 'default' && /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key));

    return {
        name: 'statamic-externals',
        enforce: 'pre',
        resolveId(id) {
            return id === 'vue' ? vueModule : null;
        },
        load(id) {
            if (id !== vueModule) {
                return null;
            }

            return `
                const Vue = window.Vue;
                export default Vue;
                export const { ${vueExports.join(', ')} } = Vue;
            `;
        },
    };
}

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
        statamicExternals(),
        laravel({
            input: [
                'resources/js/addon.js',
                'resources/css/addon.css',
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
});
