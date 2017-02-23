<template>
    <div class="media-container" :style="{minHeight: fullHeight}">
        <media-toolbar></media-toolbar>
        <div class="media-main">
            <div class="media-sidebar">
                <media-tree :root="'/'"></media-tree>
            </div>
            <media-browser></media-browser>
        </div>
        <create-folder-modal></create-folder-modal>
    </div>
</template>

<script>
    export default {
        name: 'media-app',
        methods: {
            /* Set the full height on the app container */
            setFullHeight() {
                this.fullHeight = window.innerHeight - this.$el.offsetTop + 'px';
            },
        },
        data() {
            return {
                // The full height of the app in px
                fullHeight: '',
            };
        },
        mounted() {
            // Initial load the data
            this.$store.dispatch('getContents', this.$store.state.selectedDirectory);

            // Set the full height and add event listener when dom is updated
            this.$nextTick(() => {
                this.setFullHeight();
                // Add the global resize event listener
                window.addEventListener('resize', this.setFullHeight)
            });
        },
        beforeDestroy() {
            // Remove the global resize event listener
            window.removeEventListener('resize', this.setFullHeight)
        },
    }
</script>