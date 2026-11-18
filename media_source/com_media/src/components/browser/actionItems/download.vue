<template>
  <button
    type="button"
    class="action-download"
    @click.stop="download()"
    @focus="focused(true)"
    @blur="focused(false)"
  >
    <span
      class="image-browser-action icon-download"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_DOWNLOAD') }}
    </span>
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemDownload',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    download() {
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
