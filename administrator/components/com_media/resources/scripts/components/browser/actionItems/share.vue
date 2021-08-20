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
    @keyup.up="focusUp.focus()"
    @keyup.down="focusDown.focus()"
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
  name: 'MediaBrowserActionItemShare',
  props: ['focused', 'focusUp', 'focusDown', 'mainAction', 'closingAction'],
  methods: {
    openShareUrlModal: function() {
      this.mainAction();
    },
    hideActions: function() {
      this.closingAction();
    },
    focus: function() {
      this.$refs.actionShareButton.focus()
    },
    focused: function(bool) {
      this.focused()
    }
  },
}
</script>
