<template>
  <button
    type="button"
    class="action-preview"
    aria-details="preview-desc"
    @click.stop="openPreview()"
    @keyup.enter="openPreview()"
    @keyup.space="openPreview()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-search-plus"
      aria-hidden="true"
    />
    <span
      class="action-text"
      >
      {{ translate('COM_MEDIA_ACTION_PREVIEW') }}
    </span>
  </button>
  <span
    class="sr-only"
    id="preview-desc"
  >
      {{ sprintf('COM_MEDIA_ACTION_PREVIEW_DESC', (this.$parent.$props.item.name)) }}
    </span>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemPreview',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openPreview() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};
</script>
