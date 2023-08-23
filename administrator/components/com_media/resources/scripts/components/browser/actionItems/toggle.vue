<template>
  <button
    type="button"
    class="action-toggle"
    :aria-label="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
    :title="sprintf('COM_MEDIA_MANAGE_ITEM', ($parent.$props.item.name))"
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

<script setup>
import { defineEmits } from 'vue';

const props = defineProps({
  mainAction: Function,
});

const emit = defineEmits(['on-focused']);

function openActions() {
  props.mainAction();
}

function focused(bool) {
  emit('on-focused', bool);
}
</script>
