<template>
  <MediaModal
    v-if="showPreviewModal && item"
    :size="'md'"
    class="media-preview-modal"
    label-element="previewTitle"
    :show-close="false"
    @close="close()"
  >
    <template #header>
      <h3
        id="previewTitle"
        class="modal-title text-light"
      >
        {{ item.name }}
      </h3>
    </template>
    <template #body>
      <div class="image-background">
        <audio
          v-if="isAudio()"
          controls
          :src="item.url"
        />
        <video
          v-if="isVideo()"
          controls
        >
          <source
            :src="item.url"
            :type="item.mime_type"
          >
        </video>
        <object
          v-if="isDoc()"
          :type="item.mime_type"
          :data="item.url"
          width="800"
          height="600"
        />
        <img
          v-if="isImage()"
          :src="getHashedURL"
          :type="item.mime_type"
          :style="style"
        >
      </div>
    </template>
    <template #backdrop-close>
      <button
        type="button"
        class="media-preview-close"
        @click="close()"
      >
        <span class="icon-times" />
      </button>
    </template>
  </MediaModal>
</template>

<script>
import {
  computed, watch, onMounted, ref,
} from 'vue';
import api from '../../app/Api.es6';
import MediaModal from './modal.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';

export default {
  name: 'MediaPreviewModal',
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
    /* Get the item to show in the modal */
    item() {
      // Use the currently selected directory as a fallback
      return this.store.selectedItem ? this.store.selectedItem : this.store.previewItem;
    },
    /* Get the hashed URL */
    getHashedURL() {
      if (this.item.adapter.startsWith('local-')) {
        return `${this.item.url}?${api.mediaVersion}`;
      }
      return this.item.url;
    },
    style() {
      return (this.item.mime_type !== 'image/svg+xml') ? null : 'width: clamp(300px, 1000px, 75vw)';
    },
  },
  methods: {
    /* Close the modal */
    close() {
      this.store.hidePreviewModal();
    },
    isImage() {
      return this.item.mime_type.indexOf('image/') === 0;
    },
    isVideo() {
      return this.item.mime_type.indexOf('video/') === 0;
    },
    isAudio() {
      return this.item.mime_type.indexOf('audio/') === 0;
    },
    isDoc() {
      return this.item.mime_type.indexOf('application/') === 0;
    },
  },
};
</script>
