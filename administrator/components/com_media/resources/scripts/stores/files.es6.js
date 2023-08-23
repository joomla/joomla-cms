import { defineStore } from 'pinia';
import { loadedDisks, defaultDisk, getCurrentPath } from './initials.es6.js';
import { useViewStore } from './listview.es6.js';

import api from '../app/Api.es6.js';
import dirname from '../app/path.es6.js';
import translate from '../plugins/translate.es6.js';
import notifications from '../app/Notifications.es6.js';

const updateUrlPath = (path) => {
  const currentPath = path === null ? '' : path;
  const url = new URL(window.location.href);

  if (url.searchParams.has('path')) {
    window.history.pushState(null, '', url.href.replace(/\b(path=).*?(&|$)/, `$1${currentPath}$2`));
  } else {
    window.history.pushState(null, '', `${url.href + (url.href.indexOf('?') > 0 ? '&' : '?')}path=${currentPath}`);
  }
};

/**
 * interface State {
 *   disks:             [{}]      The loaded directories
 *   directories:       [{}]      The loaded files
 *   files:             [{}]      The selected disk. Providers are ordered by plugin ordering, so we set the first provider in the list as the default provider and load first drive on it as default
 *   selectedDirectory: {String}  The currently selected items
 *   selectedItems:     [{}]      The state of the infobar
 *   previewItem:       {{}}      The contents of the preview Item
 * }
 */

const initialState = {
  disks: loadedDisks,
  directories: loadedDisks.map(() => ({
    path: defaultDisk.drives[0].root,
    name: defaultDisk.displayName,
    directories: [],
    files: [],
    directory: null,
  })),
  files: [],
  selectedDirectory: getCurrentPath(),
  selectedItems: [],
  search: '',
  previewItem: null,
};

export const useFileStore = defineStore({
  id: 'file',
  state: () => (initialState),
  getters: {
    /**
     * Get the currently selected directory
     * @param state
     * @returns {*}
     */
    getSelectedDirectory: (state) => state.directories.find((directory) => (directory.path === state.selectedDirectory)),

    /**
     * Get the sudirectories of the currently selected directory
     * @param state
     *
     * @returns {Array|directories|{/}|computed.directories|*|Object}
     */
    getSelectedDirectoryDirectories: (state) => state.directories.filter((directory) => (directory.directory === state.selectedDirectory)),

    /**
     * Get the files of the currently selected directory
     * @param state
     *
     * @returns {Array|files|{}|FileList|*}
     */
    getSelectedDirectoryFiles: (state) => state.files.filter((file) => (file.directory === state.selectedDirectory)),

    /**
     * Whether or not all items of the current directory are selected
     * @param state
     * @param getters
     * @returns Array
     */
    getSelectedDirectoryContents: (state) => ([
      ...state.directories.filter((directory) => (directory.directory === state.selectedDirectory)),
      ...state.files.filter((file) => (file.directory === state.selectedDirectory)),
    ]),

    /**
     * Get contents of a directory from the api
     * @param payload
     */
    getPathContents: (state) => (payload) => {
      // Update the url
      updateUrlPath(payload);

      // Get the view store
      const viewStore = useViewStore();

      // Set the state to loading
      viewStore.setLoading(true);

      api.getContents(payload, false, false).then((contents) => {
        state.setContent(contents);
        state.unselectBrowserItems();
        state.selectDirectory(payload);
        viewStore.setLoading(false);
      })
        .catch((error) => {
          // @todo error handling
          viewStore.setLoading(false);
          throw new Error(error);
        });
    },
  },
  actions: {
  /**
   * The load content success mutation
   * @param data
   */
    setSearchQuery(query) {
      this.search = query;
    },

    /**
   * The load content success mutation
   * @param data
   */
    setContent(data) {
      const state = this;

      /**
       * Create a directory from a path
       * @param path
       */
      function directoryFromPath(path) {
        const parts = path.split('/');
        let directory = dirname(path);

        if (directory.indexOf(':', directory.length - 1) !== -1) {
          directory += '/';
        }

        return {
          path,
          name: parts[parts.length - 1],
          directories: [],
          files: [],
          directory: (directory !== '.') ? directory : null,
          type: 'dir',
          mime_type: 'directory',
        };
      }

      /**
       * Create the directory structure
       * @param path
       */
      function createDirectoryStructureFromPath(path) {
        const exists = [...state.directories].some((existing) => (existing.path === path));

        if (exists) return;

        const directory = directoryFromPath(path);

        // Add the sub directories and files
        directory.directories = state.directories.filter((existing) => existing.directory === directory.path).map((existing) => existing.path);

        // Add the directory
        state.directories.push(directory);

        if (directory.directory) {
          createDirectoryStructureFromPath(directory.directory);
        }
      }

      /**
       * Add a directory
       * @param directory
       */
      function addDirectory(directory) {
        const parentDirectory = state.directories.find((existing) => (existing.path === directory.directory));
        const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
        let index = state.directories.findIndex((existing) => (existing.path === directory.path));

        if (index === -1) {
          index = state.directories.length;
        }

        // Add the directory
        state.directories.splice(index, 1, directory);

        // Update the relation to the parent directory
        if (parentDirectoryIndex !== -1) {
          state.directories.splice(
            parentDirectoryIndex,
            1,
            { ...parentDirectory, directories: [...parentDirectory.directories, directory.path] },
          );
        }
      }

      /**
       * Add a file
       * @param directory
       */
      function addFile(file) {
        const parentDirectory = state.directories.find((directory) => (directory.path === file.directory));
        const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
        let index = [...state.files].findIndex((existing) => (existing.path === file.path));

        if (index === -1) {
          index = state.files.length;
        }

        // Add the file
        state.files.splice(index, 1, file);

        // Update the relation to the parent directory
        if (parentDirectoryIndex !== -1) {
          state.directories.splice(
            parentDirectoryIndex,
            1,
            { ...parentDirectory, files: [...parentDirectory.files, file.path] },
          );
        }
      }

      // Create the parent directory structure if it does not exist
      createDirectoryStructureFromPath(this.selectedDirectory);

      // Add directories
      data.directories.forEach((directory) => addDirectory(directory));

      // Add files
      data.files.forEach((file) => addFile(file));
    },
    /**
     * Select a directory
     * @param payload
     */
    updateSettings() {
      localStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: this.selectedDirectory }));
    },

    /**
     * Select a directory
     * @param payload
     */
    selectDirectory(payload) {
      this.selectedDirectory = payload;
      this.search = '';

      sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ selectedDirectory: payload }));
    },

    /**
     * The upload success mutation
     * @param {{}} file
     */
    uploadSuccess(file) {
      const isNew = (!this.files.some((existing) => (existing.path === file.path)));

      // @todo handle file_exists
      if (isNew) {
        const parentDirectory = this.directories.find((existing) => (existing.path === file.directory));
        const parentDirectoryIndex = this.directories.indexOf(parentDirectory);

        // Add the new file to the files array
        this.files.push(file);

        // Update the relation to the parent directory
        this.directories.splice(
          parentDirectoryIndex,
          1,
          { ...parentDirectory, files: [...parentDirectory.files, file.path] },
        );
      }
    },

    //   /**
    //    * The create directory success mutation
    //    * @param directory
    //    */
    //   createDirectorySuccess(directory) {
    //     const isNew = (!this.directories.some((existing) => (existing.path === directory.path)));

    //     if (isNew) {
    //       const parentDirectory = this.directories.find((existing) => (existing.path === directory.directory));
    //       const parentDirectoryIndex = this.directories.indexOf(parentDirectory);

    //       // Add the new directory to the directory
    //       this.directories.push(directory);

    //       // Update the relation to the parent directory
    //       this.directories.splice(
    //         parentDirectoryIndex,
    //         1,
    //         {
    //           ...parentDirectory,
    //           directories: [...parentDirectory.directories, directory.path],
    //         },
    //       );
    //     }
    //   },

    //   /**
    //    * The rename success handler
    //    * @param payload
    //    */
    //   renameSuccess(payload) {
    //     this.selectedItems[this.selectedItems.length - 1].name = payload.newName;
    //     const { item } = payload;
    //     const { oldPath } = payload;
    //     if (item.type === 'file') {
    //       const index = this.files.findIndex((file) => (file.path === oldPath));
    //       this.files.splice(index, 1, item);
    //     } else {
    //       const index = this.directories.findIndex((directory) => (directory.path === oldPath));
    //       this.directories.splice(index, 1, item);
    //     }
    //   },

    //   /**
    //    * The delete success mutation
    //    * @param item
    //    */
    //   deleteSuccess(item) {
    //     // Delete file
    //     if (item.type === 'file') {
    //       this.files.splice(this.files.findIndex(
    //         (file) => file.path === item.path,
    //       ), 1);
    //     }

    //     // Delete dir
    //     if (item.type === 'dir') {
    //       this.directories.splice(this.directories.findIndex(
    //         (directory) => directory.path === item.path,
    //       ), 1);
    //     }
    //   },

    /**
     * Select a browser item
     * @param item the item
     */
    selectBrowserItem(item) {
      this.selectedItems.push(item);
    },

    addItemToSelectedItems(item) {
      this.selectedItems.push(item);
    },
    addItemsToSelectedItems(items) {
      items.forEach((item) => this.selectedItems.push(item));
    },
    removeItemFromSelectedItems(item) {
      this.selectedItems.splice(this.selectedItems.indexOf(item), 1);
    },
    removeItemsFromSelectedItems(items) {
      items.forEach((item) => {
        const selectedItems = this.selectedItems;
        const index = selectedItems.indexOf(item);
        if (index === -1) return;
        selectedItems.splice(index, 1);
      });
    },
    resetSelectedItems() {
      this.selectedItems = [];
    },

    toggleBrowserItemSelect(item) {
      // if (this.selectedItems.includes(item)) {
      //   this.selectedItems.splice(this.selectedItems.indexOf(item), 1);
      // } else {
      //   this.selectedItems.push(item);
      // }
    },

    /**
     * Select browser items
     * @param payload the items
     */
    selectBrowserItems(payload) {
      this.selectedItems = [];
      if (Array.isArray(payload)) {
        payload.forEach((item) => this.selectedItems.push(item));
      } else {
        this.selectedItems.push(item);
      }
    },

    /**
     * Select browser items
     * @param payload the items
     */
    selectAllBrowserItems(payload) {
      if (!payload) {
        this.selectedItems = [];
        return;
      }
      if (Array.isArray(payload)) {
        this.selectedItems = payload;
        return;
      }
    },

    /**
     * Unselect a browser item
     * @param item the item
     */
    unselectAllBrowserItems() {
      this.selectedItems = [];
    },

    /**
     * Unselect a browser item
     * @param item the item
     */
    unselectBrowserItem(item) {
      this.selectedItems.splice(this.selectedItems.findIndex((selectedItem) => selectedItem.path === item.path), 1);
    },

    /** Unselect all browser items */
    unselectBrowserItems() {
      this.selectedItems.lenght = [];
    },

    /**
     * FUll content is loaded
     * @param payload
     */
    loadFullContentsSuccess(payload) {
      this.previewItem = payload;
    },

    /**
     * Update item properties
     * @param payload object with the item, the width and the height
     */
    updateItemProperties(payload) {
      const { item, width, height } = payload;
      const index = this.files.findIndex((file) => (file.path === item.path));
      this.files[index].width = width;
      this.files[index].height = height;
    },
  },
});
