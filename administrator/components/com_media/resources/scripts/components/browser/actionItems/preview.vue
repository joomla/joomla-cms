<template>
    <button
        ref="actionPreviewButton"
        type="button"
        class="action-preview"
        :aria-label="translate('COM_MEDIA_ACTION_PREVIEW')"
        :title="translate('COM_MEDIA_ACTION_PREVIEW')"
        @keyup.enter="openPreview()"
        @keyup.space="openPreview()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.esc="hideActions()"
        @keyup.up="actionDelete.focus()"
        @keyup.down="actionDownload.focus()"
    >
        <span
            class="image-browser-action icon-search-plus"
            aria-hidden="true"
            @click.stop="openPreview()"
        />
    </button>
</template>

<script>
export default {
    name: 'MediaBrowserActionItemPreview',
    props: ['openPreview', 'focused', 'hideActions', 'actionDelete', 'actionDownload'],
    methods: {
        openPreview: function() {
            this.openPreview();
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
