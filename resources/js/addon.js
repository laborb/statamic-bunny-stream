import { markRaw as vueMarkRaw } from 'vue';
import { registerComponents } from './register.js';
import Portal from './compat/v6/Portal.vue';
import { configureMarkRaw } from './compat/reactivity.js';

Statamic.booting(() => {
    configureMarkRaw(vueMarkRaw);
    Statamic.$components.register('bunny-portal', Portal);
    registerComponents();
});
