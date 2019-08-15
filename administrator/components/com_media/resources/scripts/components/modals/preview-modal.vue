<template>
    <media-modal v-if="$store.state.showPreviewModal && item" :size="'md'" @close="close()" class="media-preview-modal" label-element="previewTitle" :show-close="false">
        <h3 slot="header" id="previewTitle" class="modal-title">{{ item.name }}</h3>
        <div slot="body">
            <img :src="item.url" v-if="isImage()" :type="item.mime_type"/>
            <video controls v-if="isVideo()">
                <source :src="item.url" :type="item.mime_type">
            </video>
        </div>
        <button type="button" slot="backdrop-close" @click="close()" class="media-preview-close">
            <span class="fa fa-times"></span>
        </button>
    </media-modal>
</template>

<script>
    import * as types from "../../store/mutation-types";

    export default {
        name: 'media-preview-modal',
        computed: {
            /* Get the item to show in the modal */
            item() {
                // Use the currently selected directory as a fallback
                return this.$store.state.previewItem;
            }
        },
        methods: {
            /* Close the modal */
            close() {
                this.$store.commit(types.HIDE_PREVIEW_MODAL);
            },
            isImage() {
                return this.item.mime_type.indexOf('image/') === 0;
            },
            isVideo() {
                return this.item.mime_type.indexOf('video/') === 0;
            }
        }
    }
</script>
