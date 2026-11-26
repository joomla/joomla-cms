<template>
  <div
    class="media-browser-item-directory"
    @mouseleave="hideActions()"
  >
    <button
      type="button"
      class="media-browser-item-preview"
      :aria-label="sprintf('COM_MEDIA_OPEN_FOLDER', item.name)"
      @dblclick.stop.prevent="onPreviewDblClick()"
      @click="onOpenControl"
    >
      <span
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
        <span
          v-if="!thumbURL"
          class="folder-icon"
        >
          <span
            class="icon-folder"
            aria-hidden="true"
          />
        </span>
      </span>
    </button>
    <div class="media-browser-item-info">
      {{ item.name }}
    </div>
    <MediaBrowserActionItemsContainer
      ref="container"
      :item="item"
      @toggle-settings="toggleSettings"
    />
  </div>
</template>
<script>
import navigable from '../../../mixins/navigable.es6';
import MediaBrowserActionItemsContainer from '../actionItems/actionItemsContainer.vue';
import { openOnNonPointerClick } from '../utils/utils.es6';

export default {
  name: 'MediaBrowserItemDirectory',
  components: {
    MediaBrowserActionItemsContainer,
  },
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      default: () => {},
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
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.navigateTo(this.item.path);

      window.parent.document.dispatchEvent(
        new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {
            type: this.item.type,
            name: this.item.name,
            path: this.item.path,
          },
        }),
      );
    },
    onOpenControl(event) {
      openOnNonPointerClick(event, this.onPreviewDblClick);
    },
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};
</script>
