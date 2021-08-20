<template>
  <div
    class="media-browser-pdf"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file-pdf" />
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
      <media-browser-action-item-toggle
          ref="actionToggle"
          :focused="focused"
          :openActions="openActions"
          :openLastActions="openLastActions"
      />
      <div
        v-if="showActions"
        class="media-browser-actions-list"
      >
        <ul>
          <li>
            <media-browser-action-item-preview
              ref="actionPreview"
              :focused="focused"
              :openPreview="openPreview"
              :hideActions="hideActions"
              :actionDelete="$refs.actionDelete"
              :actionDownload="$refs.actionDownload"
            />
          </li>
          <li>
            <media-browser-action-item-download
              ref="actionDownload"
              :focused="focused"
              :download="download"
              :hideActions="hideActions"
              :actionPreview="$refs.actionPreview"
              :actionRename="$refs.actionRename"
            />
          </li>
          <li>
            <media-browser-action-item-rename
              ref="actionRename"
              :focused="focused"
              :openRenameModal="openRenameModal"
              :hideActions="hideActions"
              :actionDownload="$refs.actionPreview"
              :actionShare="$refs.actionRename"
            />
          </li>
          <li>
            <media-browser-action-item-share
              ref="actionShare"
              :openShareUrlModal="openShareUrlModal"
              :actionRename="$refs.actionRename"
              :actionDelete="$refs.actionDelete"
              :focused="focused"
              :hideActions="hideActions"
            />
          </li>
          <li>
            <media-browser-action-item-delete
              ref="actionDelete"
              :openConfirmDeleteModal="openConfirmDeleteModal"
              :actionRename="$refs.actionRename"
              :actionDelete="$refs.actionDelete"
              :focused="focused"
              :hideActions="hideActions"
            />
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemPdf',
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
