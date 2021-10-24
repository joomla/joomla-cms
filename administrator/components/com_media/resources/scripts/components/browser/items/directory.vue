<template>
  <div class="media-browser-item-directory" @mouseleave="hideActions()">
    <div
      class="media-browser-item-preview"
      @dblclick.stop.prevent="onPreviewDblClick()"
    >
      <div class="file-background">
        <div class="folder-icon">
          <span class="icon-folder" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }}
    </div>
    <media-browser-action-items-container
      ref="container"
      :focused="focused"
      :item="item"
    />
  </div>
</template>
<script>
import navigable from "../../../mixins/navigable.es6";

export default {
  name: "MediaBrowserItemDirectory",
  mixins: [navigable],
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
      this.hideActions();
      this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(types.SHOW_RENAME_MODAL);
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
    },
  },
};
</script>
