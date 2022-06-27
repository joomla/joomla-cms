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
              {{ translate('COM_MEDIA_MEDIA_NAME') }}
            </th>
            <th
              class="size"
              scope="col"
            >
              {{ translate('COM_MEDIA_MEDIA_SIZE') }}
            </th>
            <th
              class="dimension"
              scope="col"
            >
              {{ translate('COM_MEDIA_MEDIA_DIMENSION') }}
            </th>
            <th
              class="created"
              scope="col"
            >
              {{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}
            </th>
            <th
              class="modified"
              scope="col"
            >
              {{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <media-browser-item-row
            v-for="item in items"
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
            v-for="item in items"
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

export default {
  name: 'MediaBrowser',
  computed: {
    /* Get the contents of the currently selected directory */
    items() {
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const directories = this.$store.getters.getSelectedDirectoryDirectories
        // Sort by type and alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1))
        .filter((dir) => dir.name.toLowerCase().includes(this.$store.state.search.toLowerCase()));

      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const files = this.$store.getters.getSelectedDirectoryFiles
        // Sort by type and alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1))
        .filter((file) => file.name.toLowerCase().includes(this.$store.state.search.toLowerCase()));

      return [...directories, ...files];
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
        // eslint-disable-next-line no-plusplus,no-cond-assign
        for (let i = 0, f; f = e.dataTransfer.files[i]; i++) {
          document.querySelector('.media-dragoutline').classList.remove('active');
          this.upload(f);
        }
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
