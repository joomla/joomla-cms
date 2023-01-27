<template>
  <div>
    <div
      ref="browserItems"
      class="media-browser"
      :style="mediaBrowserStyles"
      @dragenter="onDragEnter"
      @drop="onDrop"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
    >
      <div class="media-dragoutline">
        <span
          class="icon-cloud-upload upload-icon"
          aria-hidden="true"
        />
        <p>{{ translate('COM_MEDIA_DROP_FILE') }}</p>
      </div>
      <table
        v-if="listView === 'table'"
        class="table media-browser-table"
      >
        <caption class="visually-hidden">
          {{ sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', currentDirectory) }}
        </caption>
        <thead class="media-browser-table-head">
          <tr>
            <th
              class="type"
              scope="col"
            />
            <th
              class="name"
              scope="col"
            >
              <button
                class="btn btn-link"
                @click="changeOrder('name')"
              >
                {{ translate('COM_MEDIA_MEDIA_NAME') }}
                <span
                  v-if="$store.state.sortBy === 'name' && $store.state.sortDirection === 'asc'"
                  class="fa fa-arrow-down"
                  aria-hidden="true"
                />
                <span
                  v-if="$store.state.sortBy === 'name' && $store.state.sortDirection === 'desc'"
                  class="fa fa-arrow-up"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th
              class="size"
              scope="col"
            >
              <button
                class="btn btn-link"
                @click="changeOrder('size')"
              >
                {{ translate('COM_MEDIA_MEDIA_SIZE') }}
                <span
                  v-if="$store.state.sortBy === 'size' && $store.state.sortDirection === 'asc'"
                  class="fa fa-arrow-down"
                  aria-hidden="true"
                />
                <span
                  v-if="$store.state.sortBy === 'size' && $store.state.sortDirection === 'desc'"
                  class="fa fa-arrow-up"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th
              class="dimension"
              scope="col"
            >
              <button
                class="btn btn-link"
              >
                {{ translate('COM_MEDIA_MEDIA_DIMENSION') }}
              </button>
            </th>
            <th
              class="created"
              scope="col"
            >
              <button
                class="btn btn-link"
                @click="changeOrder('date_created')"
              >
                {{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}
                <span
                  v-if="$store.state.sortBy === 'date_created' && $store.state.sortDirection === 'asc'"
                  class="fa fa-arrow-down"
                  aria-hidden="true"
                />
                <span
                  v-if="$store.state.sortBy === 'date_created' && $store.state.sortDirection === 'desc'"
                  class="fa fa-arrow-up"
                  aria-hidden="true"
                />
              </button>
            </th>
            <th
              class="modified"
              scope="col"
            >
              <button
                class="btn btn-link"
                @click="changeOrder('date_modified')"
              >
                {{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}
                <span
                  v-if="$store.state.sortBy === 'date_modified' && $store.state.sortDirection === 'asc'"
                  class="fa fa-arrow-down"
                  aria-hidden="true"
                />
                <span
                  v-if="$store.state.sortBy === 'date_modified' && $store.state.sortDirection === 'desc'"
                  class="fa fa-arrow-up"
                  aria-hidden="true"
                />
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <media-browser-item-row
            v-for="item in localItems"
            :key="item.path"
            :item="item"
          />
        </tbody>
      </table>
      <div
        v-else-if="listView === 'grid'"
        class="media-browser-grid"
      >
        <div
          class="media-browser-items"
          :class="mediaBrowserGridItemsClass"
        >
          <media-browser-item
            v-for="item in localItems"
            :key="item.path"
            :item="item"
          />
        </div>
      </div>
    </div>
    <media-infobar ref="infobar" />
  </div>
</template>

<script>
import * as types from '../../store/mutation-types.es6';

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
        return a.size - b.size;
      }
      return b.size - a.size;
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
    mediaBrowserStyles() {
      return {
        width: this.$store.state.showInfoBar ? '75%' : '100%',
      };
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

    changeOrder(name) {
      this.$store.commit(types.UPDATE_SORT_BY, name);
      this.$store.commit(types.UPDATE_SORT_DIRECTION, this.$store.state.sortDirection === 'asc' ? 'desc' : 'asc');
    },
  },
};
</script>
