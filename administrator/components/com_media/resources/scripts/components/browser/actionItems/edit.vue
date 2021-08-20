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
    @keyup.up="focusUp.focus()"
    @keyup.down="focusDown.focus()"
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
  name: 'MediaBrowserActionItemEdit',
  props: ['focused', 'focusUp', 'focusDown', 'mainAction', 'closingAction'],
  methods: {
    openRenameModal: function() {
      this.mainAction();
    },
    hideActions: function() {
      this.closingAction();
    },
    focus: function() {
      this.$refs.actionPreviewButton.focus()
    },
    focused: function(bool) {
      this.focused()
    },
    editItem: function () {
      this.mainAction();
    }
  },
}
</script>
