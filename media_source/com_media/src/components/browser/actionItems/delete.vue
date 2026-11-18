<template>
  <button
    type="button"
    class="action-delete"
    @focus="focused(true)"
    @blur="focused(false)"
    @click.stop="openConfirmDeleteModal()"
  >
    <span
      class="image-browser-action icon-trash"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_DELETE') }}
    </span>
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemDelete',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openConfirmDeleteModal() {
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
