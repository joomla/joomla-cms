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
    /> 
  </div>
</template>

<script>
import * as types from '../../../store/mutation-types.es6';

export default {
  name: 'MediaBrowserItemPdf',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  computed: {
    /* Check if the item is an document to edit */
    canEdit() {
      return ['pdf'].includes(this.item.extension.toLowerCase());
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Edit an item */
    editItem() {
      this.$refs.container.editItem();
    },
  },
};
</script>
