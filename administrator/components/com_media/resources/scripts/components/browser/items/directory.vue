<template>
  <div
    class="media-browser-item-directory"
    @mouseleave="hideActions()"
  >
    <div
      class="media-browser-item-preview"
      @dblclick.stop.prevent="onPreviewDblClick()"
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
    <media-browser-action-items-container
      ref="container"
      :focused="focused"
      :item="item"
    />
  </div>
</template>
<script>
import navigable from '../../../mixins/navigable.es6';

export default {
  name: 'MediaBrowserItemDirectory',
  mixins: [navigable],
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
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
      this.$refs.container.hideActions();
    },
  },
};
</script>
