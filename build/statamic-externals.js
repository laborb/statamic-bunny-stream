export function statamicExternals(Vue) {
    const vueModule = '\0vue-external';
    const vueExports = Object.keys(Vue).filter((key) => key !== 'default' && /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key));
    const declarations = vueExports.map((key, index) => `const vueExport${index} = Vue[${JSON.stringify(key)}];`).join('\n');
    const exports = vueExports.map((key, index) => `vueExport${index} as ${key}`).join(', ');

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
                ${declarations}
                export { ${exports} };
            `;
        },
    };
}
