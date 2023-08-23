<template>
  <button
    type="button"
    class="action-delete"
    @keyup.enter="mainAction()"
    @keyup.space="mainAction()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
    @click.stop="mainAction()"
  >
    <span
      class="image-browser-action icon-trash"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_DELETE') }}
    </span>
  </button>
</template>

<script setup>
defineProps({
  onFocused: { type: Function, default: () => {} },
  mainAction: { type: Function, default: () => {} },
  closingAction: { type: Function, default: () => {} },
});

function focused(bool) {
  this.onFocused(bool);
}
</script>
