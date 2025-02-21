<template>
    <div class="flex-grow">
        <template v-if="access && hostname && library">
            <div class="flex gap-2 justify-between">
                <h1 class="flex-grow mb-4">{{ title }}</h1>
                <Uploader />
            </div>
            <VideoBrowser />
        </template>
        <template v-else>
            <Affiliate />
        </template>
    </div>
</template>

<script>
import VideoBrowser from './VideoBrowser.vue';
import Uploader from './Uploader.vue';
import SpinnerIcon from "../icons/Spinner.vue";
import Affiliate from "./Affiliate.vue";

export default {
    components: {Affiliate, SpinnerIcon, Uploader, VideoBrowser},
    props: {
        title: String,
        access: String,
        hostname: String,
        library: Number,
        routeEmbed: String,
        routeView: String,
    },
    data() {
        console.log(this.access);

        return {};
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
