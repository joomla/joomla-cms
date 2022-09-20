<template>
  <button
    type="button"
    class="action-download"
    aria-describedby="download-desc"
    @keyup.enter="download()"
    @keyup.space="download()"
    @click.stop="download()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-download"
      aria-hidden="true"
    />
    <span
          class="action-text"
          >
          {{ translate('COM_MEDIA_ACTION_DOWNLOAD') }}
        </span>
    <span
      class="sr-only"
      id="download-desc"
    >
      {{ sprintf('COM_MEDIA_ACTION_DOWNLOAD_DESC', (this.$parent.$props.item.name)) }}
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
