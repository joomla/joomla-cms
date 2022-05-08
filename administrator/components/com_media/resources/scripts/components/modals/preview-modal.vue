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
        class="modal-title text-light"
      >
        {{ item.name }}
      </h3>
    </template>
    <template #body>
      <div class="image-background">
        <audio
          v-if="isAudio()"
          controls
          :src="item.url"
        />
        <video
          v-if="isVideo()"
          controls
        >
          <source
            :src="item.url"
            :type="item.mime_type"
          >
        </video>
        <object
          v-if="isDoc()"
          :type="item.mime_type"
          :data="item.url"
          width="800"
          height="600"
        />
        <img
          v-if="isImage()"
          :src="item.url"
          :type="item.mime_type"
        >
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
    isAudio() {
      return this.item.mime_type.indexOf('audio/') === 0;
    },
    isDoc() {
      return this.item.mime_type.indexOf('application/') === 0;
    },
  },
};
</script>
