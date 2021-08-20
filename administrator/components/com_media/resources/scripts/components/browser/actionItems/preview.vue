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
        @keyup.up="focusUp.focus()"
        @keyup.down="focusDown.focus()"
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
    props: ['focused', 'focusUp', 'focusDown', 'mainAction', 'closingAction'],
    methods: {
        openPreview: function() {
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
        }
    },
}
</script>
