<template>
    <button
        ref="actionToggleButton"
        type="button"
        class="action-toggle"
        :aria-label="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
        :title="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
        @keyup.enter="openActions()"
        @focus="focused(true)"
        @blur="focused(false)"
        @keyup.space="openActions()"
        @keyup.down="openActions()"
        @keyup.up="openLastActions()"
        >
        <span
            class="image-browser-action icon-ellipsis-h"
            aria-hidden="true"
            @click.stop="openActions()"
        />
        </button>
</template>

<script>
export default {
    name: 'MediaBrowserActionItemToggle',
    props: ['openActions', 'focused', 'openLastActions'],
    methods: {
        openActions: function() {
            this.openActions();
        },
        openLastActions: function() {
            this.openLastActions();
        },
        focus: function() {
            this.$refs.actionToggleButton.focus()
        },
        focused: function(bool) {
            this.focused()
        }
    },
}
</script>