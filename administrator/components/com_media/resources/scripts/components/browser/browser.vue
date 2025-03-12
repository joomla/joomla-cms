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
    <div class="media-dragoutline">
      <span
        class="icon-cloud-upload upload-icon"
        aria-hidden="true"
      />
      <p>{{ translate('COM_MEDIA_DROP_FILE') }}</p>
    </div>
    <MediaBrowserTable
      v-if="(listView === 'table' && !isEmpty && !isEmptySearch)"
      :local-items="localItems"
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
          :local-items="localItems"
        />
      </div>
    </div>
    <MediaInfobar ref="infobar" />
  </div>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import MediaBrowserTable from './table/table.vue';
import MediaBrowserItem from './items/item.es6';
import MediaInfobar from '../infobar/infobar.vue';

function sortArray(array, by, direction) {
  return array.sort((a, b) => {
    // By name
    if (by === 'name') {
      if (direction === 'asc') {
        return a.name.toUpperCase().localeCompare(b.name.toUpperCase(), 'en', { sensitivity: 'base' });
      }
      return b.name.toUpperCase().localeCompare(a.name.toUpperCase(), 'en', { sensitivity: 'base' });
    }
    // By size
    if (by === 'size') {
      if (direction === 'asc') {
        return parseInt(a.size, 10) - parseInt(b.size, 10);
      }
      return parseInt(b.size, 10) - parseInt(a.size, 10);
    }
    // By dimension
    if (by === 'dimension') {
      if (direction === 'asc') {
        return (parseInt(a.width, 10) * parseInt(a.height, 10)) - (parseInt(b.width, 10) * parseInt(b.height, 10));
      }
      return (parseInt(b.width, 10) * parseInt(b.height, 10)) - (parseInt(a.width, 10) * parseInt(a.height, 10));
    }
    // By date created
    if (by === 'date_created') {
      if (direction === 'asc') {
        return new Date(a.create_date) - new Date(b.create_date);
      }
      return new Date(b.create_date) - new Date(a.create_date);
    }
    // By date modified
    if (by === 'date_modified') {
      if (direction === 'asc') {
        return new Date(a.modified_date) - new Date(b.modified_date);
      }
      return new Date(b.modified_date) - new Date(a.modified_date);
    }

    return array;
  });
}

export default {
  name: 'MediaBrowser',
  components: {
    MediaBrowserTable,
    MediaInfobar,
    MediaBrowserItem,
  },
  computed: {
    /* Get the contents of the currently selected directory */
    localItems() {
      const dirs = sortArray(this.$store.getters.getSelectedDirectoryDirectories.slice(0), this.$store.state.sortBy, this.$store.state.sortDirection);
      const files = sortArray(this.$store.getters.getSelectedDirectoryFiles.slice(0), this.$store.state.sortBy, this.$store.state.sortDirection);

      return [
        ...dirs.filter((dir) => dir.name.toLowerCase().includes(this.$store.state.search.toLowerCase())),
        ...files.filter((file) => file.name.toLowerCase().includes(this.$store.state.search.toLowerCase())),
      ];
    },
    /* The styles for the media-browser element */
    getHeight() {
      return {
        height: this.$store.state.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    mediaBrowserStyles() {
      return {
        width: this.$store.state.showInfoBar ? '75%' : '100%',
        height: this.$store.state.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    isEmptySearch() {
      return this.$store.state.search !== '' && this.localItems.length === 0;
    },
    isEmpty() {
      return ![...this.$store.getters.getSelectedDirectoryDirectories, ...this.$store.getters.getSelectedDirectoryFiles].length
       && !this.$store.state.isLoading;
    },
    /* The styles for the media-browser element */
    listView() {
      return this.$store.state.listView;
    },
    mediaBrowserGridItemsClass() {
      return {
        [`media-browser-items-${this.$store.state.gridSize}`]: true,
      };
    },
    isModal() {
      return Joomla.getOptions('com_media', {}).isModal;
    },
    currentDirectory() {
      const parts = this.$store.state.selectedDirectory.split('/').filter((crumb) => crumb.length !== 0);

      // The first part is the name of the drive, so if we have a folder name display it. Else
      // find the filename
      if (parts.length !== 1) {
        return parts[parts.length - 1];
      }

      let diskName = '';

      this.$store.state.disks.forEach((disk) => {
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
        this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {
                name: '',
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
        this.$store.dispatch('uploadFile', {
          name: file.name,
          parent: this.$store.state.selectedDirectory,
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
