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

<script>
import Lock from 'vue-focus-lock/src/Lock.vue';

export default {
  name: 'MediaModal',
  components: {
    Lock,
  },
  props: {
    /* Whether or not the close button in the header should be shown */
    showClose: {
      type: Boolean,
      default: true,
    },
    /* The size of the modal */
    size: {
      type: String,
      default: '',
    },
    labelElement: {
      type: String,
      required: true,
    },
  },
  emits: ['close'],
  computed: {
    /* Get the modal css class */
    modalClass() {
      return {
        'modal-sm': this.size === 'sm',
      };
    },
  },
  mounted() {
    // Listen to keydown events on the document
    document.addEventListener('keydown', this.onKeyDown);
  },
  beforeUnmount() {
    // Remove the keydown event listener
    document.removeEventListener('keydown', this.onKeyDown);
  },
  methods: {
    /* Close the modal instance */
    close() {
      this.$emit('close');
    },
    /* Handle keydown events */
    onKeyDown(event) {
      if (event.keyCode === 27) {
        this.close();
      }
    },
  },
};
</script>
