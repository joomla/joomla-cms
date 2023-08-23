import { defineStore } from 'pinia';
// import { useStorage } from '@vueuse/core'

// The grid item sizes
const modalNames = ['previewFile', 'shareFile', 'deleteFile', 'newFolder', 'rename'];
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

export const useModalStore = defineStore({
  id: 'modalStore',
  state: () => ({
    openModal: null,
  }),
  getters: {},
  actions: {
    /**
     * Toggle a modal
     * @param {string} payload
     */
    setOpenModal(payload) {
      if (!payload || (payload && !modalNames.includes(payload))) {
        this.openModal = null;
      }

      this.openModal = payload;
    },
  },
});
