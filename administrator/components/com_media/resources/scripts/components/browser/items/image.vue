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
        <div
          class="image-cropped"
          :style="{ backgroundImage: getHashedURL }"
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
    <media-browser-action-items-container
      ref="container"
      :focused="focused"
      :item="item"
      :edit="editItem"
      :editable="canEdit"
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
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    /* Get the hashed URL */
    getHashedURL() {
      if (this.item.adapter.startsWith('local-')) {
        return `url(${this.item.thumb_path}?${api.mediaVersion})`;
      }
      return `url(${this.item.thumb_path})`;
    },
  },
  methods: {
    /* Check if the item is a document to edit */
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
