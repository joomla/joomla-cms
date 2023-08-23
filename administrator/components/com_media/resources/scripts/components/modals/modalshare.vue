<template>
  <MediaModal
    v-if="showShareModal"
    :size="'md'"
    :show-close="false"
    label-element="shareTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="shareTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_SHARE') }}
      </h3>
    </template>
    <template #body>
      <div class="p-3">
        <div class="desc">
          {{ translate('COM_MEDIA_SHARE_DESC') }}

          <template v-if="!url">
            <div class="control">
              <button
                class="btn btn-success w-100"
                type="button"
                @click="generateUrl"
              >
                {{ translate('COM_MEDIA_ACTION_SHARE') }}
              </button>
            </div>
          </template>
          <template v-else>
            <div class="control">
              <span class="input-group">
                <input
                  id="url"
                  ref="urlText"
                  v-model="url"
                  readonly
                  type="url"
                  class="form-control input-xxlarge"
                  placeholder="URL"
                  autocomplete="off"
                >
                <button
                  class="btn btn-secondary"
                  type="button"
                  :title="translate('COM_MEDIA_SHARE_COPY')"
                  @click="copyToClipboard"
                >
                  <span
                    class="icon-clipboard"
                    aria-hidden="true"
                  />
                </button>
              </span>
            </div>
          </template>
        </div>
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
      </div>
    </template>
  </MediaModal>
</template>

<script>
import { computed, ref } from 'vue';
import translate from '../../plugins/translate.es6';
import MediaModal from './modal.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';

export default {
  name: 'MediaShareModal',
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
  computed: {
    item() {
      return this.selectedItems[this.store.selectedItems.length - 1];
    },

    url() {
      return (this.viewStore.previewItem && Object.prototype.hasOwnProperty.call(this.viewStore.previewItem, 'url') ? this.viewStore.previewItem.url : null);
    },
  },
  methods: {
    /* Close the modal instance and reset the form */
    close() {
      this.store.hideShareModal();
      this.store.loadFullContentsSuccess(null);
    },

    // Generate the url from backend
    generateUrl() {
      this.store.getFullContents(this.item);
    },

    // Copy to clipboard
    copyToClipboard() {
      this.$refs.urlText.focus();
      this.$refs.urlText.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        // @todo Error handling in joomla way
        window.alert(translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
      }
    },
  },
};
</script>
