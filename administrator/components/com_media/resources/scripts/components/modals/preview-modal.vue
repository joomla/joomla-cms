<template>
  <media-modal
    v-if="$store.state.showPreviewModal && item"
    :size="'md'"
    class="media-preview-modal"
    label-element="previewTitle"
    :show-close="false"
    @close="close()"
  >
    <template #header>
      <h3
        id="previewTitle"
        class="modal-title"
      >
        {{ item.name }}
      </h3>
    </template>
    <template #body>
      <div class="image-background">
        <img
          v-if="isImage()"
          :src="item.url"
          :type="item.mime_type"
        >
        <video
          v-if="isVideo()"
          controls
        >
          <source
            :src="item.url"
            :type="item.mime_type"
          >
        </video>
      </div>
    </template>
    <template #backdrop-close>
      <button
        type="button"
        class="media-preview-close"
        @click="close()"
      >
        <span class="icon-times" />
      </button>
    </template>
  </media-modal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';

export default {
  name: 'MediaPreviewModal',
  computed: {
    /* Get the item to show in the modal */
    item() {
      // Use the currently selected directory as a fallback
      return this.$store.state.previewItem;
    },
  },
  methods: {
    /* Close the modal */
    close() {
      this.$store.commit(types.HIDE_PREVIEW_MODAL);
    },
    isImage() {
      return this.item.mime_type.indexOf('image/') === 0;
    },
    isVideo() {
      return this.item.mime_type.indexOf('video/') === 0;
    },
  },
};
</script>
