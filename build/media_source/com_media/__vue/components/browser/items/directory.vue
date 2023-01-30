<template>
  <div
    class="media-browser-item-directory"
    @mouseleave="hideActions()"
  >
    <div
      class="media-browser-item-preview"
      tabindex="0"
      @dblclick.stop.prevent="onPreviewDblClick()"
      @keyup.enter="onPreviewDblClick()"
    >
      <div class="file-background">
        <div class="folder-icon">
          <span class="icon-folder" />
        </div>
      </div>
    </div>
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
  methods: {
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.navigateTo(this.item.path);
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
