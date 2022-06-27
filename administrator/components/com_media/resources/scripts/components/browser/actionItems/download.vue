<template>
  <button
    type="button"
    class="action-download"
    :aria-label="translate('COM_MEDIA_ACTION_DOWNLOAD')"
    :title="translate('COM_MEDIA_ACTION_DOWNLOAD')"
    @keyup.enter="download()"
    @keyup.space="download()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-download"
      aria-hidden="true"
      @click.stop="download()"
    />
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
