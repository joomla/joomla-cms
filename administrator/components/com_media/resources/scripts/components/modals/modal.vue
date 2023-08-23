<template>
  <div
    class="media-modal-backdrop"
    @click="close()"
  >
    <div
      class="modal"
      style="display: flex"
      @click.stop
    >
      <Lock>
        <div
          class="modal-dialog"
          :class="modalClass"
          role="dialog"
          :aria-labelledby="labelElement"
        >
          <div class="modal-content">
            <div class="modal-header">
              <slot name="header" />
              <slot name="backdrop-close" />
              <button
                v-if="showClose"
                type="button"
                class="btn-close"
                aria-label="Close"
                @click="close()"
              />
            </div>
            <div class="modal-body">
              <slot name="body" />
            </div>
            <div class="modal-footer">
              <slot name="footer" />
            </div>
          </div>
        </div>
      </Lock>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import Lock from 'vue-focus-lock/src/Lock.vue';

const props = defineProps({
  // Whether or not the close button in the header should be shown
  showClose: {
    type: Boolean,
    default: true,
  },
  // The size of the modal
  size: {
    type: String,
    default: '',
  },
  labelElement: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['close']);

// Get the modal css class
function modalClass() {
  return {
    'modal-sm': this.size === 'sm',
  };
}

// Listen to keydown events on the document
onMounted(() => {
  document.addEventListener('keydown', this.onKeyDown);
});

// Remove the keydown event listener
onBeforeUnmount(() => {
  document.removeEventListener('keydown', this.onKeyDown);
});

// Close the modal instance
function close() {
  emit('close');
}

// Handle keydown events
function onKeyDown(event) {
  if (event.key === 'Escape') {
    this.close();
  }
}
</script>
