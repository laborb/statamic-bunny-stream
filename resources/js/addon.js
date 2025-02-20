import Field from './fieldtypes/Bunny.vue';
import Overview from './components/Overview.vue';

Statamic.booting(() => {
    Statamic.$components.register('bunny-overview', Overview);
    Statamic.$components.register('bunny-fieldtype', Field);
});
