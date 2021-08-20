<template>
    <button
        ref="actionDownloadButton"
        type="button"
        class="action-download"
        :aria-label="translate('COM_MEDIA_ACTION_DOWNLOAD')"
        :title="translate('COM_MEDIA_ACTION_DOWNLOAD')"
        @keyup.enter="download()"
        @keyup.space="download()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.esc="hideActions()"
        @keyup.up="focusUp.focus()"
        @keyup.down="focusDown.focus()"
    >
        <span
            class="image-browser-action icon-download"
            aria-hidden="true"
            @click.stop="download()"
        />
    </button>
</template>

<script>
export default {
    name: 'MediaBrowserActionItemDownload',
    props: ['mainAction', 'focused', 'closingAction', 'focusUp', 'focusDown'],
    methods: {
        download: function() {
            this.mainAction();
        },
        hideActions: function() {
            this.closingAction();
        },
        focus: function() {
            this.$refs.actionDownloadButton.focus()
        },
        focused: function(bool) {
            this.focused()
        }
    },
}
</script>