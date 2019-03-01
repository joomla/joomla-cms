<template>
    <media-modal v-if="$store.state.showConfirmDeleteModal" :size="'md'" @close="close()" :show-close="false">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_CONFIRM_DELETE_MODEL_HEADING') }}</h3>
        <div slot="body">
            <div class="desc">
                {{ translate('COM_MEDIA_CONFIRM_DELETE_MODEL_DESC') }}
            </div>
        </div>
        <div slot="footer">
            <button class="btn btn-danger" @click="deleteItem()">{{ translate('COM_MEDIA_CONFIRM_DELETE_MODEL') }}</button>
            <button class="btn btn-success" @click="close()">{{ translate('JCANCEL') }}</button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";

    export default {
        name: 'media-share-modal',
        computed: {
            item() {
                // TODO @DN this is not allowed in vuex strict mode!
                return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
            }
        },
        methods: {
            /* Delete Item */
            deleteItem() {
                this.$store.dispatch('deleteItem', this.item);
                this.$store.commit(types.HIDE_CONFIRM_DELETE_MODAL);
            },
            /* Close the modal instance */
            close() {
                this.$store.commit(types.HIDE_CONFIRM_DELETE_MODAL);
            },
        }
    }
</script>
