<template>
  <div
    ref="browserItems"
    class="media-browser"
    :style="getHeight"
    @dragenter="onDragEnter"
    @drop="onDrop"
    @dragover="onDragOver"
    @dragleave="onDragLeave"
  >
    <div
      v-if="isEmptySearch"
      class="pt-1"
    >
      <div
        class="alert alert-info m-3"
      >
        <span
          class="icon-info-circle"
          aria-hidden="true"
        />
        <span
          class="visually-hidden"
        >
          {{ translate('NOTICE') }}
        </span>
        {{ translate('JGLOBAL_NO_MATCHING_RESULTS') }}
      </div>
    </div>
    <div
      v-if="isEmpty"
      class="text-center"
      style="display: grid; justify-content: center; align-content: center; margin-top: -1rem; color: var(--gray-200); height: 100%;"
    >
      <span
        class="fa-8x icon-cloud-upload upload-icon"
        aria-hidden="true"
      />
      <p>{{ translate("COM_MEDIA_DROP_FILE") }}</p>
    </div>
    <div
      ref="mmDragoutline"
      class="media-dragoutline"
    >
      <span
        class="icon-cloud-upload upload-icon"
        aria-hidden="true"
      />
      <p>{{ translate('COM_MEDIA_DROP_FILE') }}</p>
    </div>
    <MediaBrowserTable
      v-if="(listView === 'table' && !isEmpty && !isEmptySearch)"
      :items="localItems"
      :current-directory="currentDirectory"
      :style="mediaBrowserStyles"
    />
    <div
      v-if="(listView === 'grid' && !isEmpty)"
      class="media-browser-grid"
    >
      <div
        class="media-browser-items"
        :class="mediaBrowserGridItemsClass"
        :style="mediaBrowserStyles"
      >
        <MediaBrowserItem
          v-for="item in localItems"
          :key="item.path"
          :item="item"
        />
      </div>
    </div>
    <MediaInfobar ref="infobar" />
  </div>
</template>

<script>
import {
  computed, defineComponent, onMounted, ref,
} from 'vue';
import { storeToRefs } from 'pinia';
import MediaBrowserTable from './table/table.vue';
import MediaBrowserItem from './items/item.es6';
import MediaInfobar from '../infobar/infobar.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';
import sortArray from '../../app/sorting.es6';

export default {
  name: 'MediaBrowser',
  components: {
    MediaBrowserTable,
    MediaInfobar,
    MediaBrowserItem,
  },
  setup() {
    const fileStore = useFileStore();
    const disks = computed(() => fileStore.disks);
    const directories = computed(() => fileStore.directories);
    const selectedDirectory = computed(() => fileStore.selectedDirectory);
    const selectedItems = computed(() => fileStore.selectedItems);
    const search = computed(() => fileStore.search);
    const { getSelectedDirectoryDirectories, getSelectedDirectoryFiles } = storeToRefs(fileStore);

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
      sortBy,
      sortDirection,
      getSelectedDirectoryDirectories,
      getSelectedDirectoryFiles,
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
    /* Get the contents of the currently selected directory */
    localItems() {
      const dirs = sortArray(this.getSelectedDirectoryDirectories.slice(0), this.sortBy, this.sortDirection);
      const files = sortArray(this.getSelectedDirectoryFiles.slice(0), this.sortBy, this.sortDirection);

      return [
        ...dirs.filter((dir) => dir.name.toLowerCase().includes(this.search.toLowerCase())),
        ...files.filter((file) => file.name.toLowerCase().includes(this.search.toLowerCase())),
      ];
    },
    /* The styles for the media-browser element */
    getHeight() {
      return {
        height: this.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    mediaBrowserStyles() {
      return {
        width: this.infoBarState ? '75%' : '100%',
        height: this.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    isEmptySearch() {
      return this.search !== '' && this.localItems.length === 0;
    },
    isEmpty() {
      return this.localItems.length === 0 && !this.loading;
    },
    mediaBrowserGridItemsClass() {
      return {
        [`media-browser-items-${this.gridSize}`]: true,
      };
    },
    isModal() {
      return Joomla.getOptions('com_media', {}).isModal;
    },
    currentDirectory() {
      const parts = this.selectedDirectory.split('/').filter((crumb) => crumb.length !== 0);

      // The first part is the name of the drive, so if we have a folder name display it. Else
      // find the filename
      if (parts.length !== 1) {
        return parts[parts.length - 1];
      }

      let diskName = '';

      this.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root === `${parts[0]}/`) {
            diskName = drive.displayName;
          }
        });
      });

      return diskName;
    },
  },
  created() {
    document.body.addEventListener('click', this.unselectAllBrowserItems, false);
  },
  beforeUnmount() {
    document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
  },
  methods: {
    /* Unselect all browser items */
    unselectAllBrowserItems(event) {
      const clickedDelete = !!((event.target.id !== undefined && event.target.id === 'mediaDelete'));
      const notClickedBrowserItems = (this.$refs.browserItems
        && !this.$refs.browserItems.contains(event.target))
        || event.target === this.$refs.browserItems;

      const notClickedInfobar = this.$refs.infobar !== undefined
        && !this.$refs.infobar.$el.contains(event.target);

      const clickedOutside = notClickedBrowserItems && notClickedInfobar && !clickedDelete;
      if (clickedOutside) {
        this.fileStore.resetSelectedItems();

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {
                path: '',
                thumb: false,
                fileType: false,
                extension: false,
              },
            },
          ),
        );
      }
    },

    // Listeners for drag and drop
    // Fix for Chrome
    onDragEnter(e) {
      e.stopPropagation();
      return false;
    },

    // Notify user when file is over the drop area
    onDragOver(e) {
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.add('active');
      return false;
    },

    /* Upload files */
    upload(file) {
      // Create a new file reader instance
      const reader = new FileReader();

      // Add the on load callback
      reader.onload = (progressEvent) => {
        const { result } = progressEvent.target;
        const splitIndex = result.indexOf('base64') + 7;
        const content = result.slice(splitIndex, result.length);

        // Upload the file
        this.fileStore.uploadFile({
          name: file.name,
          parent: selectedDirectory,
          content,
        });
      };

      reader.readAsDataURL(file);
    },

    // Logic for the dropped file
    onDrop(e) {
      e.preventDefault();

      // Loop through array of files and upload each file
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        Array.from(e.dataTransfer.files).forEach((file) => {
          document.querySelector('.media-dragoutline').classList.remove('active');
          this.upload(file);
        });
      }
      document.querySelector('.media-dragoutline').classList.remove('active');
    },

    // Reset the drop area border
    onDragLeave(e) {
      e.stopPropagation();
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.remove('active');
      return false;
    },
  },
};
</script>
