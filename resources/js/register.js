import Field from './fieldtypes/Bunny.vue';
import Overview from './components/Overview.vue';

export function registerComponents() {
    Statamic.$components.register('bunny-overview', Overview);
    Statamic.$components.register('bunny-fieldtype', Field);
}
