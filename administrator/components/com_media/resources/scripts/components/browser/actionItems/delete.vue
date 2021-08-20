<template>
  <button
    ref="actionDeleteButton"
    type="button"
    class="action-delete"
    :aria-label="translate('COM_MEDIA_ACTION_DELETE')"
    :title="translate('COM_MEDIA_ACTION_DELETE')"
    @keyup.enter="openConfirmDeleteModal()"
    @keyup.space="openConfirmDeleteModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
    @keyup.up="$refs.actionShare.focus()"
    @keyup.down="$refs.actionPreview.focus()"
  >
    <span
      class="image-browser-action icon-trash"
      aria-hidden="true"
      @click.stop="openConfirmDeleteModal()"
    />
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemPreview',
  props: ['focused', 'hideActions', 'actionRename', 'openConfirmDeleteModal', 'actionDelete'],
  methods: {
    openRenameModal: function() {
      this.openRenameModal();
    },
    hideActions: function() {
      this.hideActions();
    },
    focus: function() {
      this.$refs.actionPreviewButton.focus()
    },
    focused: function(bool) {
      this.focused()
    }
  },
}
</script>
