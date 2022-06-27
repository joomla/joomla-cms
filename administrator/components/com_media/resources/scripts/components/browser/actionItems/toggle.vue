<template>
  <button
    type="button"
    class="action-toggle"
    tabindex="0"
    :aria-label="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
    :title="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
    @keyup.enter="openActions()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.space="openActions()"
  >
    <span
      class="image-browser-action icon-ellipsis-h"
      aria-hidden="true"
      @click.stop="openActions()"
    />
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemToggle',
  props: {
    mainAction: { type: Function, default: () => {} },
  },
  emits: ['on-focused'],
  methods: {
    openActions() {
      this.mainAction();
    },
    focused(bool) {
      this.$emit('on-focused', bool);
    },
  },
};
</script>
