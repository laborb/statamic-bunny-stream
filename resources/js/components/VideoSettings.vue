<template>
    <div>
        <ui-button class="bunny-icon-button" icon="cog" inset size="sm" variant="ghost" @click="isOpen = true" />

        <teleport to="body">
            <div
                v-if="isOpen"
                class="bunny-settings-modal"
                role="dialog"
                aria-modal="true"
                @click.self="isOpen = false"
            >
                <div class="bunny-settings-modal__panel flex flex-col">
                <header class="flex items-center justify-between rounded-t-lg border-b px-5 py-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                    {{ __('Video Settings') }}
                </header>
                <div class="space-y-4 px-5 py-6 text-gray-700 dark:text-gray-300">
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Title') }}
                        </label>
                        <ui-input id="title" v-model="videoTitle" name="title" />
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Description') }}
                        </label>
                        <textarea
                            id="description"
                            v-model="videoDescription"
                            class="bunny-textarea input-text w-full"
                            name="description"
                            rows="3"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Captions') }}
                        </label>
                        <div v-if="video.captions && video.captions.length" class="text-xs text-gray-500 dark:text-gray-400">
                            {{ video.captions.map((caption) => caption.label || caption.srclang).join(', ') }}
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <ui-input v-model="captionLanguage" :placeholder="__('Caption language')" />
                            <ui-input v-model="captionLabel" :placeholder="__('Caption label')" />
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="bunny-file-trigger" :for="captionInputId">
                                {{ __('Choose subtitle file') }}
                            </label>
                            <span class="truncate text-sm text-gray-500 dark:text-gray-400">
                                {{ captionFile ? captionFile.name : __('No file selected') }}
                            </span>
                            <input :id="captionInputId" class="sr-only" type="file" accept=".vtt,.srt" @change="selectCaption" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="thumbnail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Thumbnail') }}
                        </label>
                        <div class="bunny-thumbnail-picker">
                            <div class="bunny-thumbnail-picker__control">
                                <button
                                    id="thumbnail"
                                    class="bunny-thumbnail-picker__trigger"
                                    type="button"
                                    @click="thumbnailOpen = !thumbnailOpen"
                                >
                                    <span class="truncate">{{ selectedThumbnailLabel || __('Select new thumbnail') }}</span>
                                    <span class="bunny-thumbnail-picker__chevron" aria-hidden="true" />
                                </button>
                                <button
                                    v-if="selectedThumbnailUrl"
                                    class="bunny-thumbnail-picker__clear"
                                    type="button"
                                    :aria-label="__('Clear thumbnail selection')"
                                    @click="clearThumbnail"
                                />
                            </div>
                            <div v-if="thumbnailOpen" class="bunny-thumbnail-picker__menu">
                                <input
                                    v-model="thumbnailSearch"
                                    class="bunny-thumbnail-picker__search"
                                    type="search"
                                    :placeholder="__('Search thumbnails')"
                                />
                                <button
                                    v-for="option in filteredThumbnailOptions"
                                    :key="option.url"
                                    class="bunny-thumbnail-picker__option"
                                    type="button"
                                    @click="selectThumbnail(option.url)"
                                >
                                    {{ option.label }}
                                </button>
                                <div v-if="filteredThumbnailOptions.length === 0" class="bunny-thumbnail-picker__empty">
                                    {{ __('No thumbnails found.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <details class="space-y-2">
                        <summary class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Additional Meta Tags') }}
                        </summary>
                        <textarea
                            v-model="additionalMetaTagsText"
                            class="bunny-textarea input-text mt-2 w-full"
                            name="meta-tags"
                            rows="3"
                            placeholder="property=value"
                        />
                    </details>
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
            </div>
        </teleport>
    </div>
</template>

<script>
import axios from 'axios';
import {emitter} from '@/utils/emitter.js';

export default {
    inject: ['bunnyApiKey', 'bunnyLibrary'],
    props: {
        video: Object,
        assetOptions: Array,
    },
    data() {
        return {
            isOpen: false,
            videoTitle: this.video.title,
            videoDescription: this.getMetaTagValue('description') || this.video.description || '',
            additionalMetaTagsText: this.formatAdditionalMetaTags(this.video.metaTags),
            captionLanguage: this.video.captions?.[0]?.srclang || '',
            captionLabel: this.video.captions?.[0]?.label || '',
            captionFile: null,
            selectedThumbnailUrl: null,
            thumbnailOpen: false,
            thumbnailSearch: '',
        };
    },
    computed: {
        filteredThumbnailOptions() {
            const search = this.thumbnailSearch.trim().toLowerCase();

            if (!search) {
                return this.assetOptions.slice(0, 80);
            }

            // ponytail: render the first matches only; search narrows big asset libraries.
            return this.assetOptions.filter((option) => String(option.label || '').toLowerCase().includes(search)).slice(0, 80);
        },
        selectedThumbnailLabel() {
            return this.assetOptions.find((option) => option.url === this.selectedThumbnailUrl)?.label;
        },
        captionInputId() {
            return `caption-${this.video.guid}`;
        },
    },
    methods: {
        save() {
            if (
                this.video.title !== this.videoTitle ||
                (this.getMetaTagValue('description') || this.video.description || '') !== this.videoDescription ||
                this.formatAdditionalMetaTags(this.video.metaTags) !== this.additionalMetaTagsText
            ) {
                this.$progress.start('metadata');
                this.changeMetadata();
            }

            if (this.captionFile) {
                this.$progress.start('caption');
                this.uploadCaption();
            }

            if (this.selectedThumbnailUrl) {
                this.$progress.start('thumbnail');
                this.changeThumbnail();
            }

            this.isOpen = false;
        },
        changeMetadata() {
            const options = {
                method: 'POST',
                url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.video.guid}`,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    AccessKey: this.bunnyApiKey,
                },
                data: {
                    title: this.videoTitle,
                    metaTags: this.buildMetaTags(),
                },
            };

            axios
                .request(options)
                .then(() => {
                    this.$toast.success(__('Video metadata has been updated!'));
                    this.$progress.complete('metadata');
                    emitter.emit('load');
                })
                .catch((error) => {
                    this.$toast.error(__('An error occured while trying to update the video metadata.'));
                    this.$progress.complete('metadata');
                    console.error(error);
                });
        },
        uploadCaption() {
            if (!this.captionLanguage) {
                this.$progress.complete('caption');
                return;
            }

            const reader = new FileReader();

            reader.onload = () => {
                axios
                    .request({
                        method: 'POST',
                        url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.video.guid}/captions/${encodeURIComponent(this.captionLanguage)}`,
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            AccessKey: this.bunnyApiKey,
                        },
                        data: {
                            srclang: this.captionLanguage,
                            label: this.captionLabel,
                            captionsFile: reader.result.split(',').pop(),
                        },
                    })
                    .then(() => {
                        this.$toast.success(__('Caption has been uploaded!'));
                        this.$progress.complete('caption');
                        emitter.emit('load');
                    })
                    .catch((error) => {
                        this.$toast.error(__('An error occured while trying to upload the caption.'));
                        this.$progress.complete('caption');
                        console.error(error);
                    });
            };

            reader.readAsDataURL(this.captionFile);
        },
        changeThumbnail() {
            const options = {
                method: 'POST',
                url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.video.guid}/thumbnail?thumbnailUrl=${encodeURIComponent(this.selectedThumbnailUrl)}`,
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
                    this.selectedThumbnailUrl = null;
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
            this.selectedThumbnailUrl = value || null;
            this.thumbnailOpen = false;
            this.thumbnailSearch = '';
        },
        clearThumbnail() {
            this.selectedThumbnailUrl = null;
            this.thumbnailOpen = false;
            this.thumbnailSearch = '';
        },
        selectCaption(event) {
            this.captionFile = event.target.files[0] || null;
        },
        getMetaTagValue(property) {
            return (this.video.metaTags || []).find((tag) => tag.property === property)?.value;
        },
        formatAdditionalMetaTags(metaTags) {
            return (metaTags || [])
                .filter((tag) => tag.property !== 'description')
                .map((tag) => `${tag.property || ''}=${tag.value || ''}`)
                .join('\n');
        },
        parseAdditionalMetaTags() {
            // ponytail: Custom Bunny metadata is arbitrary metaTags, so one property=value line parser is enough.
            return this.additionalMetaTagsText
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter(Boolean)
                .map((line) => {
                    const [property, ...value] = line.split('=');

                    return {property: property.trim(), value: value.join('=').trim()};
                })
                .filter((tag) => tag.property);
        },
        buildMetaTags() {
            const tags = this.parseAdditionalMetaTags().filter((tag) => tag.property !== 'description');

            if (this.videoDescription.trim()) {
                tags.unshift({property: 'description', value: this.videoDescription.trim()});
            }

            return tags;
        },
    },
};
</script>
