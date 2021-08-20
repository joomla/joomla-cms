<template>
  <button
    ref="actionEditButton"
    type="button"
    class="action-edit"
    :aria-label="translate('COM_MEDIA_ACTION_EDIT')"
    :title="translate('COM_MEDIA_ACTION_EDIT')"
    @keyup.enter="editItem()"
    @keyup.space="editItem()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
    @keyup.up="$refs.actionRename.focus()"
    @keyup.down="$refs.actionShare.focus()"
  >
    <span
      class="image-browser-action icon-pencil-alt"
      aria-hidden="true"
      @click.stop="editItem()"
    />
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemPreview',
  props: ['focused', 'hideActions', 'actionRename', 'editItem', 'actionDelete'],
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
