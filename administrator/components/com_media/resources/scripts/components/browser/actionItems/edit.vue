<template>
  <button
    type="button"
    class="action-edit"
    :aria-label="translate('COM_MEDIA_ACTION_EDIT')"
    :title="translate('COM_MEDIA_ACTION_EDIT')"
    @keyup.enter="editItem()"
    @keyup.space="editItem()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-pencil-alt"
      aria-hidden="true"
      @click.stop="editItem()"
    />
  </button>
</template>

<script>
export default {
  name: "MediaBrowserActionItemEdit",
  props: ["focused", "mainAction", "closingAction"],
  methods: {
    openRenameModal: function () {
      this.mainAction();
    },
    hideActions: function () {
      this.closingAction();
    },
    focused: function (bool) {
      this.focused(bool);
    },
    editItem: function () {
      this.mainAction();
    },
  },
};
</script>
