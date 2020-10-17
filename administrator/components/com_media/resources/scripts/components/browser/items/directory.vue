<template>
  <div
    class="media-browser-item-directory"
    @mouseleave="hideActions()"
  >
    <div
      class="media-browser-item-preview"
      @dblclick.stop.prevent="onPreviewDblClick()"
    >
      <div class="file-background">
        <div class="folder-icon">
          <span class="fas fa-folder" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }}
    </div>
    <a
      href="#"
      class="media-browser-select"
      :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
      :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
      @click.stop="toggleSelect()"
      @focus="focused(true)"
      @blur="focused(false)"
    />
    <div
      class="media-browser-actions"
      :class="{'active': showActions}"
    >
      <button
        ref="actionToggle"
        class="action-toggle"
        type="button"
        :aria-label="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
        :title="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
        @keyup.enter="openActions()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.space="openActions()"
        @keyup.down="openActions()"
        @keyup.up="openLastActions()"
      >
        <span
          class="image-browser-action fas fa-ellipsis-h"
          aria-hidden="true"
          @click.stop="openActions()"
        />
      </button>
      <div
        v-if="showActions"
        class="media-browser-actions-list"
      >
        <ul>
          <li>
            <button
              ref="actionRename"
              type="button"
              class="action-rename"
              :aria-label="translate('COM_MEDIA_ACTION_RENAME')"
              :title="translate('COM_MEDIA_ACTION_RENAME')"
              @keyup.enter="openRenameModal()"
              @keyup.space="openRenameModal()"
              @focus="focused(true)"
              @blur="focused(false)"
              @keyup.esc="hideActions()"
              @keyup.up="$refs.actionDelete.focus()"
              @keyup.down="$refs.actionDelete.focus()"
            >
              <span
                class="image-browser-action fas fa-text-width"
                aria-hidden="true"
                @click.stop="openRenameModal()"
              />
            </button>
          </li>
          <li>
            <button
              ref="actionDelete"
              type="button"
              class="action-delete"
              :aria-label="translate('COM_MEDIA_ACTION_DELETE')"
              :title="translate('COM_MEDIA_ACTION_DELETE')"
              @keyup.enter="openConfirmDeleteModal()"
              @keyup.space="openConfirmDeleteModal()"
              @focus="focused(true)"
              @blur="focused(false)"
              @keyup.esc="hideActions()"
              @keyup.up="$refs.actionRename.focus()"
              @keyup.down="$refs.actionRename.focus()"
            >
              <span
                class="image-browser-action fas fa-trash"
                aria-hidden="true"
                @click.stop="openConfirmDeleteModal()"
              />
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
<script>
import navigable from '../../../mixins/navigable.es6';
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemDirectory',
  mixins: [navigable],
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.navigateTo(this.item.path);
    },
    /* Opening confirm delete modal */
    openConfirmDeleteModal() {
      this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_CONFIRM_DELETE_MODAL);
    },
    /* Rename an item */
    openRenameModal() {
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_RENAME_MODAL);
    },
    /* Toggle the item selection */
    toggleSelect() {
      this.$store.dispatch('toggleBrowserItemSelect', this.item);
    },
    /* Open actions dropdown */
    openActions() {
      this.showActions = true;
      this.$nextTick(() => this.$refs.actionRename.focus());
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$nextTick(() => this.$refs.actionDelete.focus());
    },
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$nextTick(() => { this.$refs.actionToggle ? this.$refs.actionToggle.focus() : false });
    },
  },
};
</script>
