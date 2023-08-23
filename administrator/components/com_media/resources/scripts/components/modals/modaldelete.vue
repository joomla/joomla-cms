<template>
  <MediaModal
    v-if="openModal"
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

<script setup>
import { computed } from 'vue';
import MediaModal from './modal.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useModalStore } from '../../stores/modalview.es6';

const filesStore = useFileStore();
const modalStore = useModalStore();
const openModal = computed(() => modalStore.openModal);

/* Delete Item */
function deleteItem() {
  filesStore.deleteSelectedItems();
  modalStore.setOpenModal(null);
}

/* Close the modal instance */
function close() {
  modalStore.setOpenModal(null);
}
</script>
