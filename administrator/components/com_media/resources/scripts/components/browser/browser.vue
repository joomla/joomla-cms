<template>
    <div class="media-browser">
        <div class="media-browser-items">
            <media-browser-item v-for="item in items" :item="item"></media-browser-item>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'media-browser',
        computed: {
            /* Filter the contents of the currently selected directory */
            items() {
                return this.$store.getters.getSelectedDirectoryContents;
            }
        }
    }
</script>