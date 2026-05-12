<template>
    <div>
        <ui-button icon="cog" inset size="xs" variant="ghost" @click="isOpen = true" />

        <modal
            v-if="isOpen"
            name="settings"
            @closed="isOpen = false"
        >
            <div class="flex h-full flex-col">
                <header class="flex items-center justify-between rounded-t-lg border-b px-5 py-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                    {{ __('Video Settings') }}
                </header>
                <div class="flex-1 space-y-4 px-5 py-6 text-gray-700 dark:text-gray-300">
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Title') }}
                        </label>
                        <ui-input id="title" v-model="videoTitle" name="title" />
                    </div>

                    <div class="space-y-2">
                        <label for="thumbnail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Thumbnail') }}
                        </label>
                        <v-select
                            ref="input"
                            :input-id="fieldId"
                            class="flex-1"
                            append-to-body
                            :clearable="false"
                            :disabled="false"
                            :options="assetOptions"
                            :placeholder="__('Select new thumbnail')"
                            :searchable="true"
                            :multiple="false"
                            :reset-on-options-change="false"
                            :close-on-select="true"
                            :model-value="selectedThumbnails"
                            @update:modelValue="selectThumbnail"
                            @focus="$emit('focus')"
                            @search:focus="$emit('focus')"
                            @search:blur="$emit('blur')"
                        >
                            <template #option="{ label }">
                                <template v-text="label"></template>
                            </template>
                            <template #selected-option="{ label }">
                                <template v-text="label"></template>
                            </template>
                            <template #no-options>
                                <div class="px-4 py-2 text-left text-sm text-gray-700" v-text="__('No options to choose from.')" />
                            </template>
                        </v-select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 rounded-b-lg border-t px-5 py-3 text-sm dark:border-gray-700">
                    <ui-button size="sm" variant="ghost" @click="isOpen = false">
                        {{ __('Cancel') }}
                    </ui-button>
                    <ui-button size="sm" variant="primary" @click="save">
                        {{ __('Save') }}
                    </ui-button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
import axios from 'axios';
import {emitter} from '@/utils/emitter.js';

export default {
    inject: ['bunnyApiKey', 'bunnyHostname', 'bunnyLibrary'],
    props: {
        id: String,
        title: String,
        assetOptions: Array,
    },
    data() {
        return {
            isOpen: false,
            videoTitle: this.title,
            selectedThumbnails: [],
        };
    },
    methods: {
        save() {
            if (this.title !== this.videoTitle) {
                this.$progress.start('title');
                this.changeTitle();
            }

            if (this.selectedThumbnails.length > 0) {
                this.$progress.start('thumbnail');
                this.changeThumbnail();
            }

            this.isOpen = false;
        },
        changeTitle() {
            const options = {
                method: 'POST',
                url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.id}`,
                headers: {
                    Accept: 'application/json',
                    'content-type': 'application/*+json',
                    AccessKey: this.bunnyApiKey,
                },
                data: '{"title":"' + this.videoTitle + '"}',
            };

            axios
                .request(options)
                .then(() => {
                    this.$toast.success(__('Video title has been updated!'));
                    this.$progress.complete('title');
                    emitter.emit('load');
                })
                .catch((error) => {
                    this.$toast.error(__('An error occured while trying to update the video title.'));
                    this.$progress.complete('title');
                    console.error(error);
                });
        },
        changeThumbnail() {
            const options = {
                method: 'POST',
                url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.id}/thumbnail?thumbnailUrl=${this.selectedThumbnails[0].url}`,
                headers: {
                    Accept: 'application/json',
                    AccessKey: this.bunnyApiKey,
                },
            };

            axios
                .request(options)
                .then(() => {
                    this.$toast.success(__('Thumbnail has been updated!'));
                    this.thumbnailUrl = null;
                    this.$progress.complete('thumbnail');
                    emitter.emit('load');
                })
                .catch((error) => {
                    this.$toast.error(__('An error occured while trying to update the thumbnail.'));
                    this.$progress.complete('thumbnail');
                    console.error(error);
                });
        },
        selectThumbnail(value) {
            this.selectedThumbnails = [value];
        },
    },
};
</script>
