import { defineStore } from 'pinia';
// import { useStorage } from '@vueuse/core'

// The grid item sizes
const gridItemSizes = ['sm', 'md', 'lg', 'xl'];
/**
 * interface State {
 *   loading:                 {Boolean},  Will hold the activated filesystem disks
 *   infoBarState:            {Boolean},  The state of the info bar
 *   listView:                {String},   List view
 *   gridSize:                {String},   The size of the grid items
 *   previewItem:             {{}},       The preview item
 *   search:                  {String},   The Search Query
 *   sortBy:                  {String}    The sorting by
 *   sortDirection:           {String}    The sorting direction
 * }
 */

export const useViewStore = defineStore({
  id: 'viewStore',
  state: () => ({
    loading: false,
    infoBarState: false,
    listView: 'grid',
    gridSize: 'md',
    sortingOptions: false,
    sortBy: 'name',
    sortDirection: 'asc',
  }),
  getters: {},
  actions: {
    setLoading(state) {
      this.loading = state;
    },

    toggleListView() {
      this.listView = this.listView === 'grid' ? 'table' : 'grid';
    },

    toggleSortingOptions() {
      this.sortingOptions = !this.sortingOptions;
    },

    toggleInfoBar() {
      this.infoBarState = !this.infoBarState;
    },

    /**
     * File content is fetched
     * @param {string} payload
     */
    setLoadFullContentsSuccess(payload) {
      this.previewItem = payload;
    },

    /**
     * Increase the size of the grid items
     */
    increaseGridSize() {
      const currentSizeIndex = gridItemSizes.indexOf(this.gridSize);
      if (currentSizeIndex >= 0 && currentSizeIndex < gridItemSizes.length - 1) {
        this.gridSize = gridItemSizes[currentSizeIndex + 1];
      }
    },

    /**
     * Decrease the size of the grid items
     */
    decreaseGridSize() {
      const currentSizeIndex = gridItemSizes.indexOf(this.gridSize);
      if (currentSizeIndex > 0 && currentSizeIndex < gridItemSizes.length) {
        this.gridSize = gridItemSizes[currentSizeIndex - 1];
      }
    },

    /**
     * Set the sorting by
     * @param payload
     */
    updateSortBy(payload) {
      this.sortBy = payload;
    },

    /**
     * Set the sorting direction
     * @param payload
     */
    updateSortDirection(payload) {
      this.sortDirection = payload === 'asc' ? 'asc' : 'desc';
    },
  },
});
