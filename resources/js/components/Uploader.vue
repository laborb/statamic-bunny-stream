<template>
    <button class="btn-primary" id="bunny-upload">
        <PlusIcon class="h-6 w-6" />
        {{ __('Upload Video') }}
    </button>
</template>

<script>
import PlusIcon from "../icons/Plus.vue";
import Uppy from '@uppy/core';
import Dashboard from '@uppy/dashboard';
import Tus from '@uppy/tus';
import { sha256 } from 'js-sha256';
import { emitter } from '@/utils/emitter.js';
import UppyBunnyCreator from '@/utils/UppyBunnyCreator.js';

export default {
    components: {
        PlusIcon
    },
    inject: ['bunnyApiKey', 'bunnyHostname', 'bunnyLibrary'],
    data() {
        return {
            expirationTime: 0,
            uploader: null
        };
    },
    methods: {
        getExpirationTime() {
            const d = new Date();
            d.setDate(d.getDate() + 1);
            return d.getTime();
        },
        getAuthorizationSignature(videoId) {
            const signature = this.bunnyLibrary + this.bunnyApiKey + this.expirationTime + videoId;
            return sha256(signature);
        },
        initializeUppy() {
            this.expirationTime = this.getExpirationTime();

            this.uploader = new Uppy()
                .use(Dashboard, {
                    inline: false,
                    trigger: '#bunny-upload',
                    width: 'auto',
                    proudlyDisplayPoweredByUppy: false,
                    closeModalOnClickOutside: true,
                    closeAfterFinish: true,
                    metaFields: [
                        { id: 'name', name: __('Name'), placeholder: __('Filename') },
                        { id: 'thumbTime', name: __('Timestamp'), placeholder: __('hh:mm:ss e.g. 00:01:04 for minute 1, second 4') },
                        {
                            id: 'bunnyId', name: __('Bunny ID'),
                            render: ({ value }, h) => {
                                return h(
                                    'input',
                                    {
                                        type: 'text',
                                        class: 'uppy-u-reset uppy-c-textInput uppy-Dashboard-FileCard-input bg-gray-300',
                                        value: value,
                                        placeholder: __('Bunny ID'),
                                        disabled: true
                                    },
                                    []
                                );
                            }
                        }
                    ],
                })
                .use(UppyBunnyCreator, {
                    access: this.bunnyApiKey,
                    library: this.bunnyLibrary
                })
                .use(Tus, {
                    endpoint: 'https://video.bunnycdn.com/tusupload',
                    retryDelays: [0, 30, 50, 3000, 5000, 10000, 60000],
                    onBeforeRequest: (req, file) => {
                        const fileMeta = this.uploader.getFile(file.id).meta;
                        if (fileMeta.bunnyId) {
                            req.setHeader('AuthorizationSignature', this.getAuthorizationSignature(fileMeta.bunnyId));
                            req.setHeader('AuthorizationExpire', this.expirationTime);
                            req.setHeader('VideoId', fileMeta.bunnyId);
                            req.setHeader('LibraryId', this.bunnyLibrary);
                        } else {
                            throw '';
                        }
                    }
                });

            this.uploader.on('complete', (result) => {
                if (result.successful.length > 0) {
                    const message = result.successful.length === 1 ? __('1 video uploaded successfully.') : __(':count videos successfully uploaded.', { count: result.successful.length });
                    console.log(message); // Replace with toast if needed
                }

                if (result.failed.length > 0) {
                    console.log('Failed files: ', result.failed);
                }

                emitter.emit('load');
            });
        }
    },
    created() {
        emitter.on('upload', () => document.getElementById('bunny-upload').click());
    },
    mounted() {
        this.initializeUppy();
    }
};
</script>
