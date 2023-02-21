<template>
  <button
    type="button"
    class="action-url"
    @click.stop="openShareUrlModal()"
    @keyup.enter="openShareUrlModal()"
    @keyup.space="openShareUrlModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-link"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_SHARE') }}
    </span>
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
