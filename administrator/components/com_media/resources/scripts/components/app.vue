<template>
    <div class="media-container row">
        <div class="media-sidebar col-md-2 hidden-sm-down">
            <media-tree :root="'/'"></media-tree>
        </div>
        <div class="media-main col-md-10">
            <div class="card">
                <div class="card-header">
                    <media-toolbar></media-toolbar>
                </div>
                <div class="card-block">
                    <media-browser></media-browser>
                </div>
            </div>
        </div>
        <create-folder-modal></create-folder-modal>
    </div>
</template>

<script>
    import * as types from "./../store/mutation-types";
    export default {
        name: 'media-app',
        created() {
            // Listen to the on click create folder event
            MediaManager.Event.listen('onClickCreateFolder', () => this.$store.commit(types.SHOW_CREATE_FOLDER_MODAL));
        },
        mounted() {
            // Initial load the data
            this.$store.dispatch('getContents', this.$store.state.selectedDirectory);
        }
    }
</script>