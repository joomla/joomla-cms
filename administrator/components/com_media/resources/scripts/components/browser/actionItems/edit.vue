<template>
  <button
    type="button"
    class="action-edit"
    @keyup.enter="editItem()"
    @keyup.space="editItem()"
    @click.stop="editItem()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-pencil-alt"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_EDIT') }}
    </span>
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemEdit',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openRenameModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
    editItem() {
      this.mainAction();
    },
  },
};
</script>
