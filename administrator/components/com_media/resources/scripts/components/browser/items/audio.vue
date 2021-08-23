<template>
  <div
    class="media-browser-audio"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file-audio" />
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
      :editItem="editItem"
      :canEdit="canEdit"
      :isPreviwable="true"
      :isDownloadable="true"
      :isShareable="true"
    />
  </div>
</template>

<script>
import * as types from "../../../store/mutation-types.es6";

export default {
  name: "MediaBrowserItemAudio",
  // eslint-disable-next-line vue/require-prop-types
  props: ["item", "focused"],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$ref.container.openPreview();
    },
  },
};
</script>
