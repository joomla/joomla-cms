<template>
  <button
    type="button"
    class="action-toggle"
    aria-haspopup="menu"
    :aria-label="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
    :title="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
    @click.stop="openActions()"
    @focus="focused(true)"
    @blur="focused(false)"
  >
    <span
      class="image-browser-action icon-ellipsis-h"
      aria-hidden="true"
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
