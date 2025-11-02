<template>
  <div
    class="media-browser-image"
    tabindex="0"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
    @keyup.enter="openPreview()"
  >
    <div
      class="media-browser-item-preview"
      :title="item.name"
    >
      <div class="image-background">
        <img
          v-if="thumbURL"
          class="image-cropped"
          :src="thumbURL"
          :alt="thumbAlt"
          :loading="thumbLoading"
          :width="thumbWidth"
          :height="thumbHeight"
          @load="setSize"
        >
        <span
          v-if="!thumbURL"
          class="icon-eye-slash image-placeholder"
          aria-hidden="true"
        />
      </div>
    </div>
    <div
      class="media-browser-item-info"
      :title="item.name"
    >
      {{ item.name }} {{ item.filetype }}
    </div>
    <span
      class="media-browser-select"
      :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
      :title="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')"
    />
    <MediaBrowserActionItemsContainer
      ref="container"
      :item="item"
      :edit="editItem"
      :previewable="true"
      :downloadable="true"
      :shareable="true"
      @toggle-settings="toggleSettings"
    />
  </div>
</template>

<script>
import api from '../../../app/Api.es6';
import MediaBrowserActionItemsContainer from '../actionItems/actionItemsContainer.vue';

export default {
  name: 'MediaBrowserItemImage',
  components: {
    MediaBrowserActionItemsContainer,
  },
  props: {
    item: { type: Object, required: true },
    focused: { type: Boolean, required: true, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: { type: Boolean, default: false },
    };
  },
  computed: {
    thumbURL() {
      if (!this.item.thumb_path) {
        return '';
      }

      return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1
        ? `${this.item.thumb_path}?${this.item.modified_date ? new Date(this.item.modified_date).valueOf() : api.mediaVersion}`
        : `${this.item.thumb_path}`;
    },
    thumbWidth() {
      return this.item.thumb_width || this.item.width || null;
    },
    thumbHeight() {
      return this.item.thumb_height || this.item.height || null;
    },
    thumbLoading() {
      return this.thumbWidth ? 'lazy' : null;
    },
    thumbAlt() {
      return this.item.name;
    },
  },
  methods: {
    /* Check if the item is an image to edit */
    canEdit() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
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
    /* Edit an item */
    editItem() {
      // @todo should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
    setSize(event) {
      if (this.item.mime_type === 'image/svg+xml') {
        const image = event.target;
        // Update the item properties
        this.$store.dispatch('updateItemProperties', { item: this.item, width: image.naturalWidth ? image.naturalWidth : 300, height: image.naturalHeight ? image.naturalHeight : 150 });
        // @TODO Remove the fallback size (300x150) when https://bugzilla.mozilla.org/show_bug.cgi?id=1328124 is fixed
        // Also https://github.com/whatwg/html/issues/3510
      }
    },
  },
};
</script>
