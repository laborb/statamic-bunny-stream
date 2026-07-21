import { registerV5UiComponents } from './compat/v5/index.js';
import { registerComponents } from './register.js';

Statamic.booting(() => {
    registerV5UiComponents();
    registerComponents();
});
