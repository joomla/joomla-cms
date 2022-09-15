<template>
  <button
    type="button"
    class="action-delete"
    :aria-label="translate('COM_MEDIA_ACTION_DELETE')"
    :title="translate('COM_MEDIA_ACTION_DELETE')"
    @keyup.enter="openConfirmDeleteModal()"
    @keyup.space="openConfirmDeleteModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
    @click.stop="openConfirmDeleteModal()"
  >
    <span
      class="image-browser-action icon-trash"
      aria-hidden="true"
    />
    <span
      class="action-text"
      aria-hidden="true"
      >
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
      this.hideActions();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};
</script>
