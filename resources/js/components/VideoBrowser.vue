<template>
    <div class="space-y-4">
        <ui-input
            v-model="search"
            :placeholder="__('Search videos')"
            class="w-full md:max-w-md"
            @update:modelValue="debouncedSearch"
        />

        <ui-card v-if="loading" class="flex justify-center py-8">
            <div role="status" class="mx-auto">
                <SpinnerIcon class="mr-2 h-8 w-8 animate-spin"/>
                <span class="sr-only">
                    {{ __('Loading...') }}
                </span>
            </div>
        </ui-card>

        <div v-else-if="result && result.totalItems >= 1" class="space-y-3">
            <VideoCard v-for="video in result.items" v-bind:key="video.guid" :video="video" :assetOptions="assetOptions" />

            <div v-if="maxPage > 1" class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-800">
                <ui-button
                    icon="chevron-left"
                    size="sm"
                    variant="default"
                    :disabled="page <= 1"
                    @click="prevPage"
                />

                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ page }} / {{ maxPage }}
                </div>

                <ui-button
                    icon="chevron-right"
                    size="sm"
                    variant="default"
                    :disabled="page >= maxPage"
                    @click="nextPage"
                />
            </div>
        </div>

        <ui-card v-else-if="search.length > 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ __('No videos found.') }}
        </ui-card>

        <button
            v-else
            class="flex w-full flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white px-6 py-10 text-center transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
            @click="openUpload()"
        >
            <PlusCircleIcon class="mb-3 h-8 w-8 text-gray-500 dark:text-gray-400" />
            <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                {{ __('Upload Video') }}
            </span>
        </button>
    </div>
</template>

<script>
import PlusCircleIcon from "../icons/PlusCircle.vue";
import SpinnerIcon from "../icons/Spinner.vue";
import VideoCard from "./VideoCard.vue";
import axios from "axios";
import {emitter} from '@/utils/emitter.js';
import debounce from "debounce";

export default {
    components: {PlusCircleIcon, SpinnerIcon, VideoCard},
    inject: ['bunnyApiKey', 'bunnyLibrary'],
    data() {
        return {
            search: '',
            loading: true,
            polling: null,
            result: null,
            page: 1,
            maxPage: 1,
            itemsPerPage: 10,
            assetOptions: [],
        };
    },
    created() {
        this.getVideos();
        this.getAssets();

        emitter.on('load', (context) => {
            if (context && context.page) {
                this.page = context.page;
            }

            this.getVideos();
        });
    },
    methods: {
        getAssets() {
            const options = {
                method: 'GET',
                url: '/cp/bunny/assets/',
                headers: {
                    Accept: 'application/json',
                },
            };

            axios
                .request(options)
                .then((response) => {
                    this.assetOptions = response.data.items;
                })
                .catch(function (error) {
                    console.error(error);
                });
        },
        getVideos() {
            const options = {
                method: 'GET',
                url: `https://video.bunnycdn.com/library/${this.bunnyLibrary}/videos?page=${this.page}&itemsPerPage=${this.itemsPerPage}&orderBy=date`,
                headers: {
                    Accept: 'application/json',
                    AccessKey: this.bunnyApiKey,
                },
            };

            if (this.search !== '') {
                options.url += '&search=' + encodeURIComponent(this.search);
            }

            axios
                .request(options)
                .then((response) => {
                    this.maxPage = Math.ceil(response.data.totalItems / this.itemsPerPage);
                    this.result = response.data;
                    this.loading = false;
                })
                .catch(function (error) {
                    console.error(error);
                });
        },
        openUpload() {
            emitter.emit('upload');
        },
        debouncedSearch: debounce(() => {
            emitter.emit('load', {page: 1});
        }, 500),
        nextPage() {
            if (this.page >= this.maxPage) {
                return;
            }

            this.page++;
            this.getVideos();
        },
        prevPage() {
            if (this.page <= 1) {
                return;
            }

            this.page--;
            this.getVideos();
        },
    },
};
</script>
