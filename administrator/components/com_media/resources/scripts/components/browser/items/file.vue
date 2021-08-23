<template>
  <div class="media-browser-item-file" @mouseleave="hideActions()">
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="icon-file-alt" />
        </div>
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
      :editItem="editItem"
      :canEdit="canEdit"
    />
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemFile',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Check if the item is an document to edit */
    canEdit() {
      return [].includes(this.item.extension.toLowerCase());
    },
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$ref.container.openPreview();
    }
    /* Edit an item */
    editItem() {},
  },
};
</script>
