<template>
    <button
        ref="actionRename"
        type="button"
        class="action-rename"
        :aria-label="translate('COM_MEDIA_ACTION_RENAME')"
        :title="translate('COM_MEDIA_ACTION_RENAME')"
        @keyup.enter="openRenameModal()"
        @keyup.space="openRenameModal()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.esc="hideActions()"
        @keyup.up="actionDownload.focus()"
        @keyup.down="actionShare.focus()"
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
    name: 'MediaBrowserActionItemPreview',
    props: ['openRenameModal', 'focused', 'hideActions', 'actionDownload', 'actionShare'],
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
