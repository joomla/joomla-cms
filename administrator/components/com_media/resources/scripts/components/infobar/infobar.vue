<template>
  <transition name="infobar">
    <div
      v-if="infoBarState && item"
      class="media-infobar"
    >
      <button
        class="infobar-close"
        @click="viewStore.toggleInfoBar"
      >
        ×
      </button>
      <h2>{{ item.name }}</h2>
      <div
        v-if="item.path === '/'"
        class="text-center"
      >
        <span class="icon-file placeholder-icon" />
        Select file or folder to view its details.
      </div>
      <dl v-else>
        <dt>{{ translate('COM_MEDIA_FOLDER') }}</dt>
        <dd>{{ item.directory }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_TYPE') }}</dt>
        <dd v-if="item.type === 'file'">
          {{ translate('COM_MEDIA_FILE') }}
        </dd>
        <dd v-else-if="item.type === 'dir'">
          {{ translate('COM_MEDIA_FOLDER') }}
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}</dt>
        <dd>{{ item.create_date_formatted }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}</dt>
        <dd>{{ item.modified_date_formatted }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</dt>
        <dd v-if="item.width || item.height">
          {{ item.width }}px * {{ item.height }}px
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_SIZE') }}</dt>
        <dd v-if="item.size">
          {{ (item.size / 1024).toFixed(2) }} KB
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_MIME_TYPE') }}</dt>
        <dd>{{ item.mime_type }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_EXTENSION') }}</dt>
        <dd>{{ item.extension || '-' }}</dd>
      </dl>
    </div>
  </transition>
</template>
<script>
import {
  computed, defineComponent, onMounted, ref,
} from 'vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';

export default {
  name: 'MediaInfobar',
  setup() {
    const fileStore = useFileStore();
    const disks = computed(() => fileStore.disks);
    const directories = computed(() => fileStore.directories);
    const selectedDirectory = computed(() => fileStore.selectedDirectory);
    const selectedItems = computed(() => fileStore.selectedItems);
    const search = computed(() => fileStore.search);

    const viewStore = useViewStore();
    const loading = computed(() => viewStore.loading);
    const infoBarState = computed(() => viewStore.infoBarState);
    const listView = computed(() => viewStore.listView);
    const gridSize = computed(() => viewStore.gridSize);
    const showConfirmDeleteModal = computed(() => viewStore.showConfirmDeleteModal);
    const showCreateFolderModal = computed(() => viewStore.showCreateFolderModal);
    const showPreviewModal = computed(() => viewStore.showPreviewModal);
    const showShareModal = computed(() => viewStore.showShareModal);
    const showRenameModal = computed(() => viewStore.showRenameModal);
    const previewItem = computed(() => viewStore.previewItem);
    const sortBy = computed(() => viewStore.sortBy);
    const sortDirection = computed(() => viewStore.sortDirection);

    return {
      disks,
      directories,
      selectedDirectory,
      selectedItems,
      search,
      fileStore,

      loading,
      infoBarState,
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
    /* Get the item to show in the infobar */
    item() {
      // If there is only one selected item, show that one.
      if (this.selectedItems.length === 1) {
        return this.selectedItems[0];
      }

      // If there are more selected items, use the last one
      if (this.selectedItems.length > 1) {
        return this.selectedItems.slice(-1)[0];
      }

      // Use the currently selected directory as a fallback
      return this.fileStore.getSelectedDirectory;
    },
  },
};
</script>
