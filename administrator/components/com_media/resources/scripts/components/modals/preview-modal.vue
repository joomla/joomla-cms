<template>
    <media-modal v-if="$store.state.showPreviewModal && item" :size="'md'" @close="close()" class="media-preview-modal">
        <h3 slot="header" class="modal-title">{{ item.name }}</h3>
        <div slot="body">
            <img :src="item.url"/>
        </div>
        <a slot="backdrop-close" @click="close()" class="media-preview-close">
            <span class="fa fa-times"></span>
        </a>
    </media-modal>
</template>

<script>
    import * as types from "../../store/mutation-types";
    import * as item from "../../components/browser/items/item";

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
            }
        }
    }
</script>
