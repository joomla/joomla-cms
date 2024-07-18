<template>
  <MediaModal
    v-if="$store.state.showConfirmDeleteModal"
    :size="'md'"
    :show-close="false"
    label-element="confirmDeleteTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="confirmDeleteTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_CONFIRM_DELETE_MODAL_HEADING') }}
      </h3>
    </template>
    <template #body>
      <div class="p-3">
        <div class="desc">
          {{ translate('JGLOBAL_CONFIRM_DELETE') }}
        </div>
      </div>
    </template>
    <template #footer>
      <div>
        <button
          class="btn btn-success"
          @click="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
        <button
          id="media-delete-item"
          class="btn btn-danger"
          @click="deleteItem()"
        >
          {{ translate('COM_MEDIA_CONFIRM_DELETE_MODAL') }}
        </button>
      </div>
    </template>
  </MediaModal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import MediaModal from './modal.vue';

export default {
  name: 'MediaShareModal',
  components: {
    MediaModal,
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
  },
  methods: {
    /* Delete Item */
    deleteItem() {
      this.$store.dispatch('deleteSelectedItems');
      this.$store.commit(types.HIDE_CONFIRM_DELETE_MODAL);
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(types.HIDE_CONFIRM_DELETE_MODAL);
    },
  },
};
</script>
