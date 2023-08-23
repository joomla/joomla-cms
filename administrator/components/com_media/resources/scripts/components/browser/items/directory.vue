<template>
  <div
    class="media-browser-item-directory"
    @mouseleave="hideActions()"
  >
    <div
      class="media-browser-item-preview"
      tabindex="0"
      @dblclick.stop.prevent="onPreviewDblClick()"
      @keyup.enter="onPreviewDblClick()"
    >
      <div class="file-background">
        <div class="folder-icon">
          <span class="icon-folder" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }}
    </div>
    <MediaBrowserActionItemsContainer
      ref="container"
      :item="item"
      @toggle-settings="toggleSettings"
    />
  </div>
</template>
<script>
import {
  computed, defineComponent, onMounted, ref,
} from 'vue';
import MediaBrowserActionItemsContainer from '../actionItems/actionItemsContainer.vue';
import { useFileStore } from '../../../stores/files.es6.js';
import { useViewStore } from '../../../stores/listview.es6.js';

export default {
  name: 'MediaBrowserItemDirectory',
  components: {
    MediaBrowserActionItemsContainer,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
  },
  emits: ['toggle-settings'],
  setup() {
    const fileStore = useFileStore();
    const disks = computed(() => fileStore.disks);
    const directories = computed(() => fileStore.directories);
    const selectedDirectory = computed(() => fileStore.selectedDirectory);
    const selectedItems = computed(() => fileStore.selectedItems);
    const search = computed(() => fileStore.search);

    const viewStore = useViewStore();
    const loading = computed(() => fileStore.loading);
    const openInfoBar = computed(() => fileStore.openInfoBar);
    const listView = computed(() => fileStore.listView);
    const gridSize = computed(() => fileStore.gridSize);
    const showConfirmDeleteModal = computed(() => fileStore.showConfirmDeleteModal);
    const showCreateFolderModal = computed(() => fileStore.showCreateFolderModal);
    const showPreviewModal = computed(() => fileStore.showPreviewModal);
    const showShareModal = computed(() => fileStore.showShareModal);
    const showRenameModal = computed(() => fileStore.showRenameModal);
    const previewItem = computed(() => fileStore.previewItem);
    const sortBy = computed(() => fileStore.sortBy);
    const sortDirection = computed(() => fileStore.sortDirection);

    return {
      disks,
      directories,
      selectedDirectory,
      selectedItems,
      search,
      fileStore,

      loading,
      openInfoBar,
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
      showActions: false,
    };
  },
  methods: {
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.fileStore.getPathContents(this.item.path, false, false);
    },
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
