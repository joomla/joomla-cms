<template>
  <div
    class="media-browser-doc"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file" />
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
  name: 'MediaBrowserItemDocument',
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
