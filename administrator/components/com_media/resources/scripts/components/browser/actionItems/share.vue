<template>
  <button
    type="button"
    class="action-url"
    :aria-label="translate('COM_MEDIA_ACTION_SHARE')"
    :title="translate('COM_MEDIA_ACTION_SHARE')"
    @keyup.enter="openShareUrlModal()"
    @keyup.space="openShareUrlModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-link"
      aria-hidden="true"
      @click.stop="openShareUrlModal()"
    />
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemShare',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openShareUrlModal() {
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
