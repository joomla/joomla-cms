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
    @keyup.up="focusUp.focus()"
    @keyup.down="focusDown.focus()"
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
  name: 'MediaBrowserActionItemDelete',
  props: ['focused', 'focusUp', 'focusDown', 'mainAction', 'closingAction'],
  methods: {
    openConfirmDeleteModal: function() {
      this.mainAction();
    },
    hideActions: function() {
      this.hideActions();
    },
    focus: function() {
      this.$refs.actionDeleteButton.focus()
    },
    focused: function(bool) {
      this.focused()
    }
  },
}
</script>
