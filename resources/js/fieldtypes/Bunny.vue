<template>
    <div>
        <v-select
            ref="input"
            :input-id="fieldId"
            class="flex-1"
            append-to-body
            :name="name"
            :clearable="false"
            :disabled="false"
            :options="options"
            :placeholder="__('Select Video...')"
            :searchable="true"
            :multiple="false"
            :reset-on-options-change="false"
            :close-on-select="true"
            :value="selectedOptions"
            @input="vueSelectUpdated"
            @focus="$emit('focus')"
            @search:focus="$emit('focus')"
            @search:blur="$emit('blur')">
                <template #option="{ label }">
                    <template v-text="label"></template>
                </template>
                <template #selected-option="{ label }">
                    <template v-text="label"></template>
                </template>
                <template #no-options>
                    <div class="text-sm text-gray-700 text-left py-2 px-4" v-text="__('No options to choose from.')" />
                </template>
        </v-select>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    mixins: [Fieldtype],
    data() {
        return {
            loading: true,
            videos: [],
            options: []
        };
    },
    computed: {
        selectedOptions() {
            let selections = this.value || [];
            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }
            return selections.map(value => {
                return _.findWhere(this.options, {value}) || { value, label: value };
            });
        },
    },
    created() {
        this.getVideos();
    },
    methods: {
        getVideos() {
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
                this.videos = response.data;
                this.loading = false;

                this.arrangeVideos();
            })
            .catch(function (error) {
                console.error(error);
            });
        },
        arrangeVideos() {
            this.videos.items.forEach((video) => {
                this.options.push({
                    value: video.guid,
                    label: video.title + ' (' + new Date(video.dateUploaded).toLocaleString() + ')'
                });
            });
        },
        focus() {
            this.$refs.input.focus();
        },
        vueSelectUpdated(value) {
            if (value) {
                this.update(value.value)
            } else {
                this.update(null);
            }
        },
    }
};
</script>
