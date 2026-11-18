<template>
  <label
    class="media-browser-select"
    :title="sprintf('COM_MEDIA_SELECT_ITEM', item.name)"
    @click.stop
    @focusin="focused(true)"
    @focusout="focused(false)"
  >
    <input
      type="checkbox"
      class="visually-hidden"
      :checked="isSelected"
      :aria-label="sprintf('COM_MEDIA_SELECT_ITEM', item.name)"
      @change="toggleSelect()"
    >
  </label>
  <div
    class="media-browser-actions"
    :class="{ active: showActions }"
  >
    <MediaBrowserActionItemToggle
      :id="toggleId"
      ref="actionToggle"
      :aria-expanded="showActions ? 'true' : 'false'"
      :aria-controls="menuId"
      :main-action="openActions"
      @on-focused="focused"
      @keydown.down.prevent="openActions()"
      @keydown.up.prevent="openLastActions()"
    />
    <div
      v-if="showActions"
      :id="menuId"
      ref="actionList"
      class="media-browser-actions-list"
      role="menu"
      :aria-label="sprintf('COM_MEDIA_ACTIONS_TOOLBAR_LABEL', item.name)"
      @keydown="onMenuKeydown"
    >
      <span
        aria-hidden="true"
        class="media-browser-actions-item-name"
      >
        <strong>{{ item.name }}</strong>
      </span>
      <MediaBrowserActionItemPreview
        v-if="previewable"
        ref="actionPreview"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="openPreview"
        :closing-action="hideActions"
      />
      <MediaBrowserActionItemDownload
        v-if="downloadable"
        ref="actionDownload"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="download"
        :closing-action="hideActions"
      />
      <MediaBrowserActionItemRename
        v-if="canEdit"
        ref="actionRename"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="openRenameModal"
        :closing-action="hideActions"
      />
      <MediaBrowserActionItemEdit
        v-if="canEdit && canOpenEditView"
        ref="actionEdit"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="editItem"
        :closing-action="hideActions"
      />
      <MediaBrowserActionItemShare
        v-if="shareable"
        ref="actionShare"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="openShareUrlModal"
        :closing-action="hideActions"
      />
      <MediaBrowserActionItemDelete
        v-if="canDelete"
        ref="actionDelete"
        role="menuitem"
        tabindex="-1"
        :on-focused="focused"
        :main-action="openConfirmDeleteModal"
        :closing-action="hideActions"
      />
    </div>
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';
import api from '../../../app/Api.es6';
import { toggleItemSelection } from '../utils/utils.es6';

import MediaBrowserActionItemEdit from './edit.vue';
import MediaBrowserActionItemDelete from './delete.vue';
import MediaBrowserActionItemDownload from './download.vue';
import MediaBrowserActionItemPreview from './preview.vue';
import MediaBrowserActionItemRename from './rename.vue';
import MediaBrowserActionItemShare from './share.vue';
import MediaBrowserActionItemToggle from './toggle.vue';

let menuCounter = 0;

export default {
  name: 'MediaBrowserActionItemsContainer',
  components: {
    MediaBrowserActionItemEdit,
    MediaBrowserActionItemDelete,
    MediaBrowserActionItemDownload,
    MediaBrowserActionItemPreview,
    MediaBrowserActionItemRename,
    MediaBrowserActionItemShare,
    MediaBrowserActionItemToggle,
  },
  props: {
    item: { type: Object, default: () => {} },
    edit: { type: Function, default: () => {} },
    previewable: { type: Boolean, default: false },
    downloadable: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    menuCounter += 1;

    return {
      showActions: false,
      menuUid: menuCounter,
    };
  },
  computed: {
    /* Unique ids for the menu button and the menu */
    toggleId() {
      return `media-actions-toggle-${this.menuUid}`;
    },
    menuId() {
      return `media-actions-menu-${this.menuUid}`;
    },
    isSelected() {
      return this.$store.state.selectedItems.some((selected) => selected.path === this.item.path);
    },
    canEdit() {
      return api.canEdit && (typeof this.item.canEdit !== 'undefined' ? this.item.canEdit : true);
    },
    canDelete() {
      return api.canDelete && (typeof this.item.canDelete !== 'undefined' ? this.item.canDelete : true);
    },
    canOpenEditView() {
      // @TODO pass the array of allowed to edit files from PHP
      return api.hasMediaActionPlugins && ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
  },
  watch: {
    '$store.state.showRenameModal': function (show) {
      if (
        !show
        && this.$refs.actionToggle
        && this.$store.state.selectedItems.find(
          (item) => item.name === this.item.name,
        ) !== undefined
      ) {
        this.$refs.actionToggle.$el.focus();
      }
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$parent.$parent.$data.actionsActive = false;
    },
    /* Preview an item */
    openPreview() {
      this.$store.commit(types.SHOW_PREVIEW_MODAL);
      this.$store.dispatch('getFullContents', this.item);
    },
    /* Download an item */
    download() {
      this.$store.dispatch('download', this.item);
    },
    /* Opening confirm delete modal */
    openConfirmDeleteModal() {
      this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_CONFIRM_DELETE_MODAL);
    },
    /* Rename an item */
    openRenameModal() {
      this.hideActions();
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_RENAME_MODAL);
    },
    /* Open modal for share url */
    openShareUrlModal() {
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_SHARE_MODAL);
    },
    /* Open actions menu and focus the first item */
    openActions() {
      this.showMenu('first');
    },
    /* Open actions menu and focus the last item */
    openLastActions() {
      this.showMenu('last');
    },
    /* Show the menu, then move the focus into it once it has been rendered */
    showMenu(position) {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      this.$nextTick(() => {
        const items = this.getMenuItems();
        if (items.length) {
          items[position === 'last' ? items.length - 1 : 0].focus();
        }
      });
    },
    getMenuItems() {
      return this.$refs.actionList ? [...this.$refs.actionList.querySelectorAll('[role="menuitem"]')] : [];
    },
    /* Close the menu and return the focus to the menu button */
    closeMenuAndFocusToggle() {
      this.hideActions();
      this.$nextTick(() => {
        if (this.$refs.actionToggle) {
          this.$refs.actionToggle.$el.focus();
        }
      });
    },
    /* Keyboard support of the menu: arrows, Home, End, Escape and Tab */
    onMenuKeydown(event) {
      const items = this.getMenuItems();
      const index = items.indexOf(document.activeElement);
      let next = null;

      switch (event.key) {
        case 'ArrowDown':
          next = (index + 1) % items.length;
          break;
        case 'ArrowUp':
          next = (index - 1 + items.length) % items.length;
          break;
        case 'Home':
          next = 0;
          break;
        case 'End':
          next = items.length - 1;
          break;
        case 'Escape':
          event.preventDefault();
          event.stopPropagation();
          this.closeMenuAndFocusToggle();
          return;
        case 'Tab':
          this.hideActions();
          return;
        default:
          return;
      }

      event.preventDefault();
      items[next].focus();
    },
    toggleSelect() {
      toggleItemSelection(this.item, this.$store);
    },
    editItem() {
      this.edit();
    },
    focused(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
