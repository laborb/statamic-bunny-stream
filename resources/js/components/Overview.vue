<template>
    <div>
        <div class="flex gap-2 justify-between">
            <h1 class="flex-grow mb-4">{{ title }}</h1>
            <Uploader />
        </div>
        <VideoBrowser />
    </div>
</template>

<script>
import VideoBrowser from './VideoBrowser.vue';
import Uploader from './Uploader.vue';
import SpinnerIcon from "../icons/Spinner.vue";

export default {
    components: {SpinnerIcon, Uploader, VideoBrowser},
    props: {
        title: String,
        access: String,
        hostname: String,
        library: Number,
        routeEmbed: String,
        routeView: String,
    },
    provide() {
        return {
            bunnyApiKey: this.access,
            bunnyHostname: this.hostname,
            bunnyLibrary: this.library,
            routeEmbed: this.routeEmbed,
            routeView: this.routeView,
        };
    }
}
</script>
