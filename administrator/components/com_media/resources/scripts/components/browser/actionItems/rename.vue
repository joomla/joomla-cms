<template>
  <button
    ref="actionRenameButton"
    type="button"
    class="action-rename"
    aria-details="rename-desc"
    @click.stop="openRenameModal()"
    @keyup.enter="openRenameModal()"
    @keyup.space="openRenameModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action fa fa-i-cursor"
      aria-hidden="true"
    />
    <span
      class="action-text"
      >
      {{ translate('COM_MEDIA_ACTION_RENAME') }}
    </span>

  </button>
  <span
    class="sr-only"
    id="rename-desc"
  >
      {{ sprintf('COM_MEDIA_ACTION_RENAME_DESC', (this.$parent.$props.item.name)) }}
    </span>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemRename',
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
  },
};
</script>
