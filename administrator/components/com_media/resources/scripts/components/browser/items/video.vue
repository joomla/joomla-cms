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
    <span
      class="media-browser-select"
      :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
      :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    />
    <div
      class="media-browser-actions"
      :class="{'active': showActions}"
    >
      <button
        ref="actionToggle"
        type="button"
        class="action-toggle"
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
          class="image-browser-action icon-ellipsis-h"
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
              ref="actionPreview"
              type="button"
              class="action-preview"
              :aria-label="translate('COM_MEDIA_ACTION_PREVIEW')"
              :title="translate('COM_MEDIA_ACTION_PREVIEW')"
              @keyup.enter="openPreview()"
              @keyup.space="openPreview()"
              @focus="focused(true)"
              @blur="focused(false)"
              @keyup.esc="hideActions()"
              @keyup.up="$refs.actionDelete.focus()"
              @keyup.down="$refs.actionDownload.focus()"
            >
              <span
                class="image-browser-action icon-search-plus"
                aria-hidden="true"
                @click.stop="openPreview()"
              />
            </button>
          </li>
          <li>
            <button
              ref="actionDownload"
              type="button"
              class="action-download"
              :aria-label="translate('COM_MEDIA_ACTION_DOWNLOAD')"
              :title="translate('COM_MEDIA_ACTION_DOWNLOAD')"
              @keyup.enter="download()"
              @keyup.space="download()"
              @focus="focused(true)"
              @blur="focused(false)"
              @keyup.esc="hideActions()"
              @keyup.up="$refs.actionPreview.focus()"
              @keyup.down="$refs.actionRename.focus()"
            >
              <span
                class="image-browser-action icon-download"
                aria-hidden="true"
                @click.stop="download()"
              />
            </button>
          </li>
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
              @keyup.up="$refs.actionDownload.focus()"
              @keyup.down="$refs.actionShare.focus()"
            >
              <span
                class="image-browser-action icon-text-width"
                aria-hidden="true"
                @click.stop="openRenameModal()"
              />
            </button>
          </li>
          <li>
            <button
              ref="actionShare"
              type="button"
              class="action-url"
              :aria-label="translate('COM_MEDIA_ACTION_SHARE')"
              :title="translate('COM_MEDIA_ACTION_SHARE')"
              @keyup.enter="openShareUrlModal()"
              @keyup.space="openShareUrlModal()"
              @focus="focused(true)"
              @blur="focused(false)"
              @keyup.esc="hideActions()"
              @keyup.up="$refs.actionRename.focus()"
              @keyup.down="$refs.actionDelete.focus()"
            >
              <span
                class="image-browser-action icon-link"
                aria-hidden="true"
                @click.stop="openShareUrlModal()"
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
              @keyup.up="$refs.actionShare.focus()"
              @keyup.down="$refs.actionPreview.focus()"
            >
              <span
                class="image-browser-action icon-trash"
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
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemVideo',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Preview an item */
    openPreview() {
      this.$store.commit(types.SHOW_PREVIEW_MODAL);
      this.$store.dispatch('getFullContents', this.item);
    },
    /* Preview an item */
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
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_RENAME_MODAL);
    },
    /* Open modal for share url */
    openShareUrlModal() {
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_SHARE_MODAL);
    },
    /* Open actions dropdown */
    openActions() {
      this.showActions = true;
      this.$nextTick(() => this.$refs.actionPreview.focus());
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$nextTick(() => this.$refs.actionDelete.focus());
    },
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$nextTick(() => this.$refs.actionToggle.focus());
    },
  },
};
</script>
