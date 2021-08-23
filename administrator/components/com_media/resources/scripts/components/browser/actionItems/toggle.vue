<template>
  <button
    type="button"
    class="action-toggle"
    :aria-label="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
    :title="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')"
    @keyup.enter="openActions()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.space="openActions()"
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
  name: "MediaBrowserActionItemToggle",
  props: ["mainAction", "focused"],
  methods: {
    openActions: function () {
      this.mainAction();
    },
    focused: function (bool) {
      this.focused(bool);
    },
  },
};
</script>
