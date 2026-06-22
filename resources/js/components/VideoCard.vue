<template>
    <div>
        <ui-card
            v-if="localVideo.status >= 4"
            class="h-full overflow-hidden p-0!"
        >
            <div class="sm:grid sm:grid-cols-3 overflow-hidden">
                <a :href="videoUrl" target="_blank" class="block">
                    <img :src="thumbnailUrl" class="aspect-video inset-0 h-full object-cover w-full" />
                </a>
                <div class="sm:col-span-2 flex flex-col justify-between gap-3 px-4 py-4 sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <a :href="videoUrl" target="_blank" class="min-w-0 grow truncate text-base leading-tight font-semibold text-gray-900 dark:text-gray-100 sm:text-lg">
                            {{ localVideo.title }}
                        </a>
                        <div class="flex items-center gap-1.5">
                            <VideoSettings :video="localVideo" :assetOptions="assetOptions" />
                            <button
                                ref="deleteButton"
                                class="bunny-icon-button bunny-icon-button--danger"
                                type="button"
                                :aria-label="__('Delete video :title', {title: localVideo.title})"
                                @click.stop="confirmDeletion"
                            >
                                <TrashIcon />
                            </button>
                        </div>
                    </div>

                    <p class="flex flex-wrap gap-2 text-xs whitespace-nowrap md:gap-4 md:text-sm">
                        <a v-if="viewUrl" :href="viewUrl" target="_blank" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                            {{ __('Direct Play') }}
                            <LinkIcon class="size-5" />
                        </a>
                        <a v-if="embedUrl" :href="embedUrl" target="_blank" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                            {{ __('Embed URL') }}
                            <LinkIcon class="size-5" />
                        </a>
                        <a :href="thumbnailUrl" target="_blank" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                            {{ __('Thumbnail') }}
                            <LinkIcon class="size-5" />
                        </a>
                    </p>

                    <div class="flex justify-between gap-4 text-xs text-gray-500 dark:text-gray-400 md:text-sm">
                        <div class="flex items-center gap-2">
                            <CloudIcon class="size-5 text-gray-500" />
                            {{ new Date(localVideo.dateUploaded).toLocaleString() }}
                        </div>
                        <div class="flex items-center gap-2">
                            <EyeIcon class="size-5 text-gray-500" />
                            {{ localVideo.views }}
                        </div>
                    </div>
                </div>
            </div>
        </ui-card>
        <ui-card
            v-else
            class="flex h-full flex-col items-center justify-center gap-3 text-center"
        >
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Video is being processed') }} &ndash; {{ encodeProgress }}%
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('This may take some time.') }}
            </p>
            <button ref="deleteButton" class="btn-danger text-xs" type="button" @click="confirmDeletion">
                {{ __('Cancel and delete video') }}
            </button>
            <div role="status" class="mx-auto mt-2">
                <SpinnerIcon class="mr-2 h-8 w-8 animate-spin"/>
                <span class="sr-only">{{ __('Loading...') }}</span>
            </div>
        </ui-card>

        <teleport to="body">
            <div
                v-if="deletionConfirmationStep > 0"
                class="bunny-settings-modal"
                role="dialog"
                aria-modal="true"
                @click.self="cancelDeletion"
            >
                <div class="bunny-settings-modal__panel bunny-delete-modal flex flex-col">
                    <header class="rounded-t-lg border-b px-5 py-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        {{ deletionConfirmationTitle }}
                    </header>
                    <div class="flex items-center justify-end gap-3 rounded-b-lg px-5 py-3 text-sm">
                        <ui-button size="sm" variant="ghost" @click="cancelDeletion">
                            {{ __('Cancel') }}
                        </ui-button>
                        <ui-button size="sm" variant="danger" @click="confirmDeletionStep">
                            {{ deletionConfirmationButton }}
                        </ui-button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script>
import CloudIcon from "../icons/Cloud.vue";
import EyeIcon from "../icons/Eye.vue";
import LinkIcon from "../icons/Link.vue";
import SpinnerIcon from "../icons/Spinner.vue";
import TrashIcon from "../icons/Trash.vue";
import VideoSettings from "./VideoSettings.vue";
import axios from "axios";
import {emitter} from '@/utils/emitter.js';

export default {
    components: {VideoSettings, CloudIcon, LinkIcon, SpinnerIcon, EyeIcon, TrashIcon},
    inject: ['bunnyApiKey', 'bunnyHostname', 'bunnyLibrary', 'routeEmbed', 'routeView'],
    props: {
        video: Object,
        assetOptions: Array,
        loading: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localVideo: this.video,
            embedUrl: this.routeEmbed ? this.routeEmbed.replace(':video:', this.video.guid) : null,
            viewUrl: this.routeView ? this.routeView.replace(':video:', this.video.guid) : null,
            thumbnailUrl: `https://${this.bunnyHostname}/${this.video.guid}/${this.video.thumbnailFileName}`,
            videoUrl: `https://iframe.mediadelivery.net/play/${this.video.videoLibraryId}/${this.video.guid}`,
            deletionConfirmationStep: 0,
        }
    },
    computed: {
        encodeProgress() {
            const progress = Number(this.localVideo.encodeProgress) || 0;

            return Math.min(100, Math.max(0, Math.round(progress)));
        },
        deletionConfirmationTitle() {
            if (this.deletionConfirmationStep === 2) {
                return __('Permanently delete :title from Bunny?', {title: this.localVideo.title});
            }

            return __('Delete video :title', {title: this.localVideo.title});
        },
        deletionConfirmationButton() {
            return this.deletionConfirmationStep === 2 ? __('Delete from Bunny') : __('Continue');
        },
    },
    mounted() {
        this.$refs.deleteButton?.addEventListener('click', this.confirmDeletion);

        if (this.localVideo.status < 4) {
            this.polling = setInterval(() => {
                this.loadVideo();
            }, 5000);
        }
    },
    beforeUnmount() {
        this.$refs.deleteButton?.removeEventListener('click', this.confirmDeletion);
        clearInterval(this.polling);
    },
    methods: {
        confirmDeletion() {
            this.deletionConfirmationStep = 1;
        },
        confirmDeletionStep() {
            if (this.deletionConfirmationStep < 2) {
                this.deletionConfirmationStep += 1;
                return;
            }

            this.deleteVideo();
        },
        loadVideo() {
            this.loading = true;

            axios
                .request({
                    method: 'GET',
                    url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.localVideo.guid}`,
                    headers: {
                        Accept: 'application/json',
                        AccessKey: this.bunnyApiKey,
                    },
                })
                .then(response => {
                    this.localVideo = response.data;
                    if (this.localVideo.status >= 4) {
                        clearInterval(this.polling);
                        emitter.emit('load');
                    }
                })
                .catch(function (error) {
                    console.error(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        deleteVideo() {
            this.loading = true;

            axios
                .request({
                    method: 'DELETE',
                    url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos/${this.localVideo.guid}`,
                    headers: {
                        Accept: 'application/json',
                        AccessKey: this.bunnyApiKey,
                    },
                })
                .then(() => {
                    this.cancelDeletion();
                    clearInterval(this.polling);
                    emitter.emit('load');
                })
                .catch(function (error) {
                    console.error(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        cancelDeletion() {
            this.deletionConfirmationStep = 0;
        },
    }
}
</script>
