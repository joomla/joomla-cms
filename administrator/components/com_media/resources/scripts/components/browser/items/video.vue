<template>
  <div
    class="media-browser-image"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file-video" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }} {{ item.filetype }}
    </div>
    <MediaBrowserActionItemsContainer
      ref="container"
      :item="item"
      :previewable="true"
      :downloadable="true"
      :shareable="true"
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
  name: 'MediaBrowserItemVideo',
  components: {
    MediaBrowserActionItemsContainer,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
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
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
