<template>
  <button
    type="button"
    class="action-toggle"
    :aria-label="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
    :title="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
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
