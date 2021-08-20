<template>
  <button
    ref="actionShareButton"
    type="button"
    class="action-url"
    :aria-label="translate('COM_MEDIA_ACTION_SHARE')"
    :title="translate('COM_MEDIA_ACTION_SHARE')"
    @keyup.enter="openShareUrlModal()"
    @keyup.space="openShareUrlModal()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
    @keyup.up="actionRename.focus()"
    @keyup.down="actionDelete.focus()"
  >
    <span
      class="image-browser-action icon-link"
      aria-hidden="true"
      @click.stop="openShareUrlModal()"
    />
  </button>
</template>

<script>
export default {
  name: 'MediaBrowserActionItemPreview',
  props: ['focused', 'hideActions', 'actionRename', 'openShareUrlModal', 'actionDelete'],
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
