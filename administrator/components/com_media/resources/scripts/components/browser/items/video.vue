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
    <media-browser-action-items-container
      ref="container"
      :focused="focused"
      :item="item"
      :previewable="true"
      :downloadable="true"
      :shareable="true"
    />
  </div>
</template>

<script>
export default {
  name: "MediaBrowserItemVideo",
  // eslint-disable-next-line vue/require-prop-types
  props: ["item", "focused"],
  data() {
    return {
      showActions: false,
    };
  },
  watch: {
    // eslint-disable-next-line
    "$store.state.showRenameModal"(show) {
      if (
        !show &&
        this.$refs.actionToggle &&
        this.$store.state.selectedItems.find(
          (item) => item.name === this.item.name
        ) !== undefined
      ) {
        this.$refs.actionToggle.focus();
      }
    },
  },
  methods: {
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
      this.hideActions();
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
    },
  },
};
</script>
