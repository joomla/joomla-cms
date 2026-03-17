<template>
  <div
    class="media-browser-audio"
    tabindex="0"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
    @keyup.enter="openPreview()"
  >
    <div class="media-browser-item-preview">
      <div
        class="file-background"
        :class="{ 'with-thumbnail': thumbURL }"
      >
        <img
          v-if="thumbURL"
          class="image-cropped"
          alt=""
          :src="thumbURL"
          :loading="thumbLoading"
          :width="thumbWidth"
          :height="thumbHeight"
        >
        <div
          v-if="!thumbURL"
          class="file-icon"
        >
          <span class="fas fa-file-audio" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }} {{ item.filetype }}
    </div>
    <MediaBrowserActionItemsContainer
      ref="container"
      :item="item"
      :previewable="true"
      :downloadable="true"
      :shareable="true"
      @toggle-settings="toggleSettings"
    />
  </div>
</template>

<script>
import MediaBrowserActionItemsContainer from '../actionItems/actionItemsContainer.vue';

export default {
  name: 'MediaBrowserItemAudio',
  components: {
    MediaBrowserActionItemsContainer,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    thumbURL() {
      let path = this.item.thumb_path || '';

      if (path && this.item.modified_date) {
        path = path + (path.includes('?') ? '&' : '?') + this.item.modified_date;
      }

      return path;
    },
    thumbWidth() {
      return this.item.thumb_width || null;
    },
    thumbHeight() {
      return this.item.thumb_height || null;
    },
    thumbLoading() {
      return this.item.thumb_width ? 'lazy' : null;
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
