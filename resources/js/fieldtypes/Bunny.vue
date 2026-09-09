<template>
    <div>
        <ui-select
            ref="input"
            :input-id="fieldId"
            class="flex-1"
            :name="name"
            :clearable="false"
            :disabled="false"
            :options="options"
            option-label="label"
            option-value="value"
            :placeholder="__('Select Video...')"
            :searchable="true"
            :model-value="selectedValue"
            @update:modelValue="uiSelectUpdated"
            @focus="$emit('focus')"
        />
    </div>
</template>

<script>
import axios from 'axios';

const FieldtypeMixin = window.__STATAMIC__?.core?.FieldtypeMixin || window.Fieldtype;
const videoTitleCollator = new Intl.Collator(undefined, {
    numeric: true,
    sensitivity: 'base'
});

export default {
    mixins: [FieldtypeMixin],
    data() {
        return {
            loading: true,
            videos: [],
            options: []
        };
    },
    computed: {
        selectedValue() {
            let selection = this.value;

            if (Array.isArray(selection)) {
                selection = selection[0] ?? null;
            }

            if (selection === null || selection === undefined || selection === '') {
                return null;
            }

            return selection;
        },
    },
    created() {
        this.getVideos();
    },
    methods: {
        getVideos() {
            if (!this.meta.api || !this.meta.library) {
                this.loading = false;
                this.options = [];
                return;
            }

            const options = {
                method: 'GET',
                url: 'https://video.bunnycdn.com/library/' + this.meta.library + '/videos?page=1&itemsPerPage=100&orderBy=date',
                headers: {
                    accept: 'application/json',
                    AccessKey: this.meta.api
                }
            };

            axios
            .request(options)
            .then((response) => {
                this.videos = response.data?.items || [];
                this.loading = false;

                this.arrangeVideos();
            })
            .catch((error) => {
                this.loading = false;
                this.options = [];
                console.error(error);
            });
        },
        arrangeVideos() {
            this.options = [...this.videos]
                .sort((a, b) => videoTitleCollator.compare(a.title || '', b.title || ''))
                .map((video) => ({
                    value: video.guid,
                    label: video.title + ' (' + new Date(video.dateUploaded).toLocaleString() + ')'
                }));
        },
        focus() {
            this.$refs.input?.focus?.();
        },
        uiSelectUpdated(value) {
            this.update(value || null);
        },
    }
};
</script>
