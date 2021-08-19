<template>
    <button
        ref="actionToggle"
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
        focused: function(focused) {
            this.focused(focused);
        },
        openLastActions: function() {
            this.openLastActions();
        }
    },
}
</script>