<template>
  <button
    type="button"
    class="action-preview"
    @click.stop="openPreview()"
    @keyup.enter="openPreview()"
    @keyup.space="openPreview()"
    @focus="focused(true)"
    @blur="focused(false)"
    @keyup.esc="hideActions()"
  >
    <span
      class="image-browser-action icon-search-plus"
      aria-hidden="true"
    />
    <span class="action-text">
      {{ translate('COM_MEDIA_ACTION_PREVIEW') }}
    </span>
  </button>
</template>

<script setup>
defineProps({
  onFocused: { type: Function, default: () => {} },
  mainAction: { type: Function, default: () => {} },
  closingAction: { type: Function, default: () => {} },
});

function openPreview() {
  this.mainAction();
}

function hideActions() {
  this.closingAction();
}

function focused(bool) {
  this.onFocused(bool);
}
</script>
