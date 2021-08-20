<template>
  <media-browser-action-item-toggle
    ref="actionToggle"
    :focused="focused"
    :mainAction="openActions"
    :focusUp="openLastActions"
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
          :mainAction="openPreview"
          :closingAction="hideActions"
          :focusUp="$refs.actionDelete"
          :focusDown="$refs.actionDownload"
        />
      </li>
      <li>
        <media-browser-action-item-download
          ref="actionDownload"
          :focused="focused"
          :mainAction="download"
          :closingAction="hideActions"
          :focusUp="$refs.actionPreview"
          :focusDown="$refs.actionRename"
        />
      </li>
      <li>
        <media-browser-action-item-rename
          ref="actionRename"
          :focused="focused"
          :mainAction="openRenameModal"
          :closingAction="hideActions"
          :focusUp="$refs.actionDownload"
          :focusDown="canEdit ? $refs.actionEdit : $refs.actionShare"
        />
      </li>
      <li v-if="canEdit">
        <media-browser-action-item-edit
          ref="actionEdit"
          :focused="focused"
          :mainAction="editItem"
          :closingAction="hideActions"
          :focusUp="$refs.actionRename"
          :focusDown="$refs.actionShare"
        />
      </li>
      <li>
        <media-browser-action-item-share
          ref="actionShare"
          :focused="focused"
          :mainAction="openShareUrlModal"
          :focusUp="canEdit ? $refs.actionEdit : $refs.actionRename"
          :focusDown="$refs.actionDelete"
          :closingAction="hideActions"
        />
      </li>
      <li>
        <media-browser-action-item-delete
          ref="actionDelete"
          :focused="focused"
          :mainAction="openConfirmDeleteModal"
          :hideActions="hideActions"
          :focusUp="$refs.actionShare"
          :focusDown="$refs.actionPreview"
        />
      </li>
    </ul>
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemPdf',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused', 'canEdit'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    /* Check if the item is an document to edit */
    canEdit() {
      return this.canEdit();
    },
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
    editItem() {
      // TODO should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
    focused() {
      this.focused();
    }
  },
};
</script>
