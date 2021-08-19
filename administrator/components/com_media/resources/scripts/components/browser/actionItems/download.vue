<template>
    <button
        ref="actionDownload"
        type="button"
        class="action-download"
        :aria-label="translate('COM_MEDIA_ACTION_DOWNLOAD')"
        :title="translate('COM_MEDIA_ACTION_DOWNLOAD')"
        @keyup.enter="download()"
        @keyup.space="download()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.esc="hideActions()"
        @keyup.up="actionPreview.focus()"
        @keyup.down="actionRename.focus()"
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
    props: ['download', 'focused', 'hideActions', 'actionPreview', 'actionRename'],
    methods: {
        download: function() {
            this.download();
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