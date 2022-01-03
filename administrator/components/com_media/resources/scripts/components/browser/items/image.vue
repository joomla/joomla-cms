<template>
  <div
    class="media-browser-image"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="image-background">
        <div
          v-observe-visibility="visibilityChanged"
          class="image-cropped"
          :style="{ backgroundImage: currentInfo }"
        />
      </div>
    </div>
    <div class="media-browser-item-info">
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
  props: {
    item: {
      type: Object,
      required: true,
    },
    focused: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  data() {
    return {
      showActions: {
        type: Boolean,
        default: false,
      },
      currentInfo: {
        type: Object || Boolean,
        required: true,
        default: false,
      },
    };
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
      // TODO should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
    visibilityChanged(isVisible, entry) {
      if (entry.isIntersecting) {
        this.currentInfo = this.item.adapter.startsWith('local-')
          ? `url(${this.item.thumb_path}?${api.mediaVersion})`
          : `url(${this.item.thumb_path})`;
      } else {
        this.currentInfo = false;
      }
    },
  },
};
</script>
