<template>
  <button
    type="button"
    class="action-delete"
    aria-details="delete-desc"
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
      >
      {{ translate('COM_MEDIA_ACTION_DELETE') }}
    </span>
  </button>
  <span
    class="sr-only"
    id="delete-desc"
  >
      {{ sprintf('COM_MEDIA_ACTION_DELETE_DESC', (this.$parent.$props.item.name)) }}
    </span>
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
