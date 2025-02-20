<template>
    <div>
        <div class="container w-100 lg:w-4/5 mx-auto input-group mb-4">
            <input type="text" :placeholder="__('Search videos')" @input="debouncedSearch" v-model="search" class="input-text" />
        </div>
        <div v-if="loading" class="flex items-center">
            <div role="status" class="mt-4 mx-auto">
                <SpinnerIcon class="w-8 h-8 mr-2 animate-spin"/>
                <span class="sr-only">
                    {{ __('Loading...') }}
                </span>
            </div>
        </div>
        <div v-else-if="!loading && result.totalItems >= 1">
            <div id="card">
                <div class="container w-100 lg:w-4/5 mx-auto">
                    <div class="flex flex-col">
                        <VideoCard v-for="video in result.items" v-bind:key="video.guid" :video="video" :assetOptions="assetOptions" />
                    </div>

                    <div v-if="result.totalItems > result.items.length" class="flex items-center justify-between">
                        <button class="btn-primary" @click="prevPage">
                            &laquo;
                        </button>

                        <div>
                            {{ page }} / {{ maxPage }}
                        </div>

                        <button class="btn-primary" @click="nextPage">
                            &raquo;
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="this.search.length > 0" class="text-center text-sm">
            {{ __('No videos found.') }}
        </div>
        <button v-else @click="openUpload()" class="flex flex-col overflow-hidden border-gray-600 border-dashed border-2 rounded shadow-xl w-full mb-4 p-6 items-center justify-center">
            <h2 class="text-xl">
                {{ __('Upload Video') }}
            </h2>
            <PlusCircleIcon class="h-10 w-10" />
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
                options.url += '&search=' + this.search;
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
            this.page++;
            if (this.page > this.maxPage) {
                this.page = 1;
            }

            this.getVideos();
        },
        prevPage() {
            this.page--;
            if (this.page <= 0) {
                this.page = this.maxPage;
            }

            this.getVideos();
        },
    },
};
</script>
