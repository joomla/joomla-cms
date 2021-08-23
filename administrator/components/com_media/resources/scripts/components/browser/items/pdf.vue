<template>
  <div
    class="media-browser-pdf"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file-pdf" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }} {{ item.filetype }}
    </div>
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
  name: 'MediaBrowserItemPdf',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  methods: {
    /* Check if the item is an document to edit */
    canEdit() {
      return [].includes(this.item.extension.toLowerCase());
    },
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Edit an item */
    editItem() {
      // TODO should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
  },
};
</script>
