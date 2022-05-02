<template>
  <div
    class="media-browser-image"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div
      class="media-browser-item-preview"
      :title="item.name"
    >
      <div class="image-background">
        <img
          v-if="getURL"
          class="image-cropped"
          :src="getURL"
          :alt="altTag"
          loading="lazy"
          :width="width"
          :height="height"
        >
        <span v-if="!getURL" class="icon-eye-slash image-placeholder" aria-hidden="true"></span>
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
    <media-browser-action-items-container
      ref="container"
      :focused="focused"
      :item="item"
      :edit="editItem"
      :previewable="true"
      :downloadable="true"
      :shareable="true"
    />
  </div>
</template>

<script>
import { api } from '../../../app/Api.es6';

export default {
  name: 'MediaBrowserItemImage',
  props: {
    item: { type: Object, required: true },
    focused: { type: Boolean, required: true, default: false },
  },
  data() {
    return {
      showActions: { type: Boolean, default: false },
    };
  },
  computed: {
    getURL() {
      if (!this.item.thumb_path) {
        return '';
      }

      return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1
        ? `${this.item.thumb_path}?${api.mediaVersion}`
        : `${this.item.thumb_path}`;
    },
    width() {
      return this.item.width;
    },
    height() {
      return this.item.height;
    },
    altTag() {
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
      this.$refs.container.hideActions();
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
  },
};
</script>
