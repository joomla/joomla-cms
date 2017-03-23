<template>
    <div class="media-container row no-gutters" :style="{minHeight: fullHeight}">
        <div class="media-sidebar col-md-2 hidden-sm-down">
            <media-tree :root="'/'"></media-tree>
        </div>
        <div class="media-main col-md-10">
            <media-toolbar></media-toolbar>
            <media-browser></media-browser>
            <media-infobar></media-infobar>
        </div>
        <media-create-folder-modal></media-create-folder-modal>
    </div>
</template>

<script>
    import * as types from "./../store/mutation-types";
    export default {
        name: 'media-app',
        data() {
            return {
                // The full height of the app in px
                fullHeight: '',
            };
        },
        methods: {
            /* Set the full height on the app container */
            setFullHeight() {
                this.fullHeight = window.innerHeight - this.$el.getBoundingClientRect().top + 'px';
            },
        },
        created() {
            // Listen to the on click create folder event
            MediaManager.Event.listen('onClickCreateFolder', () => this.$store.commit(types.SHOW_CREATE_FOLDER_MODAL));
        },
        mounted() {
            // Set the full height and add event listener when dom is updated
            this.$nextTick(() => {
                this.setFullHeight();
                // Add the global resize event listener
                window.addEventListener('resize', this.setFullHeight)
            });

            // Initial load the data
            this.$store.dispatch('getContents', this.$store.state.selectedDirectory);
        },
        beforeDestroy() {
            // Remove the global resize event listener
            window.removeEventListener('resize', this.setFullHeight)
        },
    }
</script>