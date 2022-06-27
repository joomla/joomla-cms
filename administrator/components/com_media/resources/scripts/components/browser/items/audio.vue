<template>
  <div
    class="media-browser-audio"
    tabindex="0"
    @dblclick="openPreview()"
    @mouseleave="hideActions()"
    @keyup.enter="openPreview()"
  >
    <div class="media-browser-item-preview">
      <div class="file-background">
        <div class="file-icon">
          <span class="fas fa-file-audio" />
        </div>
      </div>
    </div>
    <div class="media-browser-item-info">
      {{ item.name }} {{ item.filetype }}
    </div>
    <media-browser-action-items-container
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
export default {
  name: 'MediaBrowserItemAudio',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
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
