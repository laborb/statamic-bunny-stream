<template>
    <div class="space-y-4">
        <template v-if="access && hostname && library">
            <ui-header :title="title">
                <Uploader />
            </ui-header>
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
import Affiliate from "./Affiliate.vue";

export default {
    components: {Affiliate, Uploader, VideoBrowser},
    props: {
        title: String,
        access: String,
        hostname: String,
        library: Number,
        routeEmbed: String,
        routeView: String,
    },
    data() {
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
