<template>
  <button
    ref="actionRenameButton"
    type="button"
    class="action-rename"
    :aria-label="translate('COM_MEDIA_ACTION_RENAME')"
    :title="translate('COM_MEDIA_ACTION_RENAME')"
    @keyup.enter="openRenameModal()"
    @keyup.space="openRenameModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-text-width"
      aria-hidden="true"
      @click.stop="openRenameModal()"
    />
  </button>
</template>

<script>
export default {
  name: "MediaBrowserActionItemRename",
  props: ["mainAction", "focused", "closingAction"],
  methods: {
    openRenameModal: function () {
      this.mainAction();
    },
    hideActions: function () {
      this.closingAction();
    },
    focused: function (bool) {
      this.focused(bool);
    },
  },
};
</script>
