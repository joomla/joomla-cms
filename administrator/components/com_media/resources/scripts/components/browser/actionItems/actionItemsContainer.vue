<template>
  <span
    class="media-browser-select"
    :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
  />
  <div class="media-browser-actions" :class="{ active: showActions }">
    <media-browser-action-item-toggle
      ref="actionToggle"
      :focused="focused"
      :mainAction="openActions"
      @keyup.up="openLastActions()"
      @keyup.down="openActions()"
    />
    <div v-if="showActions" class="media-browser-actions-list">
      <ul>
        <li>
          <media-browser-action-item-preview
            ref="actionPreview"
            :focused="focused"
            :mainAction="openPreview"
            :closingAction="hideActions"
            @keyup.up="$refs.actionDelete.$el.focus()"
            @keyup.down="$refs.actionDownload.$el.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-download
            ref="actionDownload"
            :focused="focused"
            :mainAction="download"
            :closingAction="hideActions"
            @keyup.up="$refs.actionPreview.$el.focus()"
            @keyup.down="$refs.actionRename.$el.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-rename
            ref="actionRename"
            :focused="focused"
            :mainAction="openRenameModal"
            :closingAction="hideActions"
            @keyup.up="$refs.actionDownload.$el.focus()"
            @keyup.down="
              canEdit
                ? $refs.actionEdit.$el.focus()
                : $refs.actionShare.$el.focus()
            "
          />
        </li>
        <li>
          <media-browser-action-item-edit
            v-if="canEdit"
            ref="actionEdit"
            :focused="focused"
            :mainAction="editItem"
            :closingAction="hideActions"
            @keyup.up="$refs.actionRename.$el.focus()"
            @keyup.down="$refs.actionShare.$el.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-share
            ref="actionShare"
            :focused="focused"
            :mainAction="openShareUrlModal"
            :closingAction="hideActions"
            @keyup.up="
              canEdit
                ? $refs.actionEdit.$el.focus()
                : $refs.actionRename.$el.focus()
            "
            @keyup.down="$refs.actionDelete.$el.focus()"
          />
        </li>
        <li>
          <media-browser-action-item-delete
            ref="actionDelete"
            :focused="focused"
            :mainAction="openConfirmDeleteModal"
            :hideActions="hideActions"
            @keyup.up="$refs.actionShare.$el.focus()"
            @keyup.down="$refs.actionPreview.$el.focus()"
          />
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import * as types from "../../../store/mutation-types.es6";

export default {
  name: "MediaBrowserActionItemsContainer",
  // eslint-disable-next-line vue/require-prop-types
  // props: ["item", "focused", "editItem", "canEdit"],
  props: {
    item: Object,
    focused: Function,
    editItem: { type: Function, default: () => {} },
    canEdit: { type: Function, default: () => false },
    isPreviwable: { type: Boolean, default: false },
    isDownloadable: { type: Boolean, default: false },
    isShareable: { type: Boolean, default: false },
  },
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
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$nextTick(() => this.$refs.actionToggle.$el.focus());
    },
    /* Preview an item */
    openPreview() {
      this.$store.commit(types.SHOW_PREVIEW_MODAL);
      this.$store.dispatch("getFullContents", this.item);
    },
    /* Preview an item */
    download() {
      this.$store.dispatch("download", this.item);
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
      this.$nextTick(() => this.$refs.actionPreview.$el.focus());
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$nextTick(() => this.$refs.actionDelete.$el.focus());
    },
    editItem() {
      this.editItem();
    },
  },
};
</script>
