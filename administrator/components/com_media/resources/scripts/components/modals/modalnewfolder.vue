<template>
  <MediaModal
    v-if="showCreateFolderModal"
    :size="'md'"
    label-element="createFolderTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="createFolderTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}
      </h3>
    </template>
    <template #body>
      <div class="p-3">
        <form
          class="form"
          novalidate
          @submit.prevent="save"
        >
          <div class="form-group">
            <label for="folder">{{ translate('COM_MEDIA_FOLDER_NAME') }}</label>
            <input
              id="folder"
              ref="input"
              v-model.trim="folder"
              class="form-control"
              type="text"
              required
              autocomplete="off"
              @input="folder = $event.target.value"
            >
          </div>
        </form>
      </div>
    </template>
    <template #footer>
      <div>
        <button
          class="btn btn-secondary"
          @click="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
        <button
          class="btn btn-success"
          :disabled="!isValid()"
          @click="save()"
        >
          {{ translate('JACTION_CREATE') }}
        </button>
      </div>
    </template>
  </MediaModal>
</template>

<script>
import {
  computed, defineComponent, onMounted, ref,
} from 'vue';
import MediaModal from './modal.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';

export default {
  name: 'MediaCreateFolderModal',
  components: {
    MediaModal,
  },
  setup() {
    const filesStore = useFileStore();
    const disks = computed(() => filesStore.disks);
    const directories = computed(() => filesStore.directories);
    const selectedDirectory = computed(() => filesStore.selectedDirectory);
    const selectedItems = computed(() => filesStore.selectedItems);
    const search = computed(() => filesStore.search);

    const viewStore = useViewStore();
    const isLoading = computed(() => filesStore.isLoading);
    const showInfoBar = computed(() => filesStore.showInfoBar);
    const listView = computed(() => filesStore.listView);
    const gridSize = computed(() => filesStore.gridSize);
    const showConfirmDeleteModal = computed(() => filesStore.showConfirmDeleteModal);
    const showCreateFolderModal = computed(() => filesStore.showCreateFolderModal);
    const showPreviewModal = computed(() => filesStore.showPreviewModal);
    const showShareModal = computed(() => filesStore.showShareModal);
    const showRenameModal = computed(() => filesStore.showRenameModal);
    const previewItem = computed(() => filesStore.previewItem);
    const sortBy = computed(() => filesStore.sortBy);
    const sortDirection = computed(() => filesStore.sortDirection);

    return {
      disks,
      directories,
      selectedDirectory,
      selectedItems,
      search,
      filesStore,

      isLoading,
      showInfoBar,
      listView,
      gridSize,
      showConfirmDeleteModal,
      showCreateFolderModal,
      showPreviewModal,
      showShareModal,
      showRenameModal,
      previewItem,
      sortBy,
      sortDirection,
      viewStore,
    };
  },
  data() {
    return {
      folder: '',
    };
  },
  watch: {
    'store.showCreateFolderModal': function (show) {
      this.$nextTick(() => {
        if (show && this.$refs.input) {
          this.$refs.input.focus();
        }
      });
    },
  },
  methods: {
    /* Check if the the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Close the modal instance */
    close() {
      this.reset();
      this.store.hideCreateFolderModal();
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo show an error message to user for insert a folder name
        // @todo mark the field as invalid
        return;
      }

      // Create the directory
      this.store.createDirectory({
        name: this.folder,
        parent: this.store.selectedDirectory(),
      });
      this.reset();
    },
    /* Reset the form */
    reset() {
      this.folder = '';
    },
  },
};
</script>
