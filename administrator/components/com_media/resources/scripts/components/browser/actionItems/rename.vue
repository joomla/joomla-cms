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
        @keyup.up="focusUp.focus()"
        @keyup.down="focusDown.focus()"
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
    name: 'MediaBrowserActionItemRename',
    props: ['mainAction', 'focused', 'closingAction', 'focusUp', 'focusDown'],
    methods: {
        openRenameModal: function() {
            this.mainAction();
        },
        hideActions: function() {
            this.closingAction();
        },
        focus: function() {
            this.$refs.actionRenameButton.focus()
        },
        focused: function(bool) {
            this.focused()
        }
    },
}
</script>
