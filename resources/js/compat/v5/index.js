import CogIcon from '../../icons/Cog.vue';

const UiButton = {
    inheritAttrs: false,
    components: { CogIcon },
    props: {
        disabled: Boolean,
        href: String,
        icon: String,
        inset: Boolean,
        size: String,
        target: String,
        type: {
            type: String,
            default: 'button',
        },
        variant: {
            type: String,
            default: 'default',
        },
    },
    computed: {
        classes() {
            if (this.icon) {
                return ['bunny-v5-icon-button'];
            }

            return [
                'btn',
                this.size === 'sm' ? 'btn-sm' : null,
                this.variant === 'primary' ? 'btn-primary' : null,
                this.variant === 'danger' ? 'btn-danger' : null,
            ];
        },
    },
    template: `
        <component
            :is="href ? 'a' : 'button'"
            v-bind="$attrs"
            :class="classes"
            :disabled="disabled"
            :href="href"
            :target="target"
            :type="href ? null : type"
            @click="$emit('click', $event)"
        >
            <CogIcon v-if="icon === 'cog'" />
            <span v-else-if="icon === 'chevron-left'" aria-hidden="true">&#8249;</span>
            <span v-else-if="icon === 'chevron-right'" aria-hidden="true">&#8250;</span>
            <slot />
        </component>
    `,
};

const UiCard = {
    template: '<div class="card"><slot /></div>',
};

const UiHeader = {
    props: {
        title: String,
    },
    template: `
        <header class="mb-4 flex flex-wrap items-center justify-between gap-4 py-4">
            <h1 class="text-2xl font-bold">{{ title }}</h1>
            <div><slot /></div>
        </header>
    `,
};

const UiHeading = {
    props: {
        href: String,
        level: {
            type: Number,
            default: 2,
        },
        text: String,
    },
    computed: {
        tag() {
            return this.href ? 'a' : `h${this.level}`;
        },
    },
    template: '<component :is="tag" :href="href" class="font-bold"><slot>{{ text }}</slot></component>',
};

const UiInput = {
    inheritAttrs: false,
    props: {
        modelValue: {
            default: undefined,
        },
        value: {
            default: undefined,
        },
    },
    computed: {
        inputValue() {
            return this.modelValue === undefined ? this.value : this.modelValue;
        },
    },
    methods: {
        update(event) {
            this.$emit('input', event.target.value);
            this.$emit('update:modelValue', event.target.value);
        },
    },
    template: `
        <input
            v-bind="$attrs"
            class="input-text"
            :value="inputValue"
            @input="update"
            @change="$emit('change', $event)"
            @focus="$emit('focus', $event)"
            @blur="$emit('blur', $event)"
        >
    `,
};

const UiSelect = {
    inheritAttrs: false,
    props: {
        clearable: Boolean,
        disabled: Boolean,
        inputId: String,
        modelValue: {
            default: null,
        },
        name: String,
        optionLabel: {
            type: String,
            default: 'label',
        },
        optionValue: {
            type: String,
            default: 'value',
        },
        options: {
            type: Array,
            default: () => [],
        },
        placeholder: String,
        searchable: Boolean,
    },
    computed: {
        selectedOption() {
            return this.options.find((option) => option[this.optionValue] === this.modelValue) || null;
        },
    },
    methods: {
        focus() {
            this.$refs.select?.focus?.();
        },
        update(option) {
            this.$emit('update:modelValue', option ? option[this.optionValue] : null);
        },
    },
    template: `
        <v-select
            ref="select"
            v-bind="$attrs"
            append-to-body
            :clearable="clearable"
            :disabled="disabled"
            :input-id="inputId"
            :name="name"
            :options="options"
            :placeholder="placeholder"
            :searchable="searchable"
            :value="selectedOption"
            @input="update"
            @focus="$emit('focus', $event)"
            @search:focus="$emit('focus', $event)"
            @search:blur="$emit('blur', $event)"
        />
    `,
};

const BunnyPortal = {
    template: '<portal name="bunny-stream"><slot /></portal>',
};

export function registerV5UiComponents() {
    Statamic.$components.register('bunny-portal', BunnyPortal);
    Statamic.$components.register('ui-button', UiButton);
    Statamic.$components.register('ui-card', UiCard);
    Statamic.$components.register('ui-header', UiHeader);
    Statamic.$components.register('ui-heading', UiHeading);
    Statamic.$components.register('ui-input', UiInput);
    Statamic.$components.register('ui-select', UiSelect);
}
