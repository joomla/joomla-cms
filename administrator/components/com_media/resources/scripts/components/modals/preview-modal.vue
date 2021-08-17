<template>
  <media-modal
    v-if="$store.state.showPreviewModal && item"
    :size="'md'"
    class="media-preview-modal"
    label-element="previewTitle"
    :show-close="false"
    @open="open()"
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
      <div
        v-if="isVideo()"
        class="player-wrapper"
      >
        <video
          id="video-player"
          width="100%"
          height="100%"
          style="width: 100%; height: 300px;"
          :src="item.url"
          :type="item.mime_type"
        />
      </div>
      <div
        v-if="isAudio()"
        class="player-wrapper"
      >
        <audio
          id="audio-player"
          width="100%"
          height="100%"
          style="width: 100%;"
          :src="item.url"
          :type="item.mime_type"
        />
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
  watch: {
    // Handle item change from ordinary to potentially playable
    item(newItem, oldItem) {
      if (oldItem && oldItem.mime_type !== newItem.mime_type) {
        setTimeout(() => this.initPlayer());
      }
    },
  },
  methods: {
    open() {
      this.initPlayer();
    },
    /* Close the modal */
    close() {
      this.$store.commit(types.HIDE_PREVIEW_MODAL);
    },
    initPlayer() {
      /* eslint-disable no-undef */
      if (typeof MediaElementPlayer !== 'undefined') {
        // Start player depending on media type
        if (this.isVideo()) {
          this.player = new MediaElementPlayer('video-player');
        } else if (this.isAudio()) {
          this.player = new MediaElementPlayer('audio-player');
        }
      }
      /* eslint-enable no-undef */
    },
    destroyPlayer() {
      if (this.player) {
        this.player.remove();
        this.player = null;
      }
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
