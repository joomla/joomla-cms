<template>
  <button
    type="button"
    class="action-preview"
    @click.stop="openPreview()"
    @focus="focused(true)"
    @blur="focused(false)"
  >
    <span
      class="image-browser-action icon-search-plus"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_PREVIEW') }}
    </span>
  </button>
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
