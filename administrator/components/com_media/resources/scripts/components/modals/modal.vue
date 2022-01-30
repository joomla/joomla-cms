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
      <tab-lock>
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
                @open="opened()"
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
      </tab-lock>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MediaModal',
  props: {
    /* Whether or not the close button in the header should be shown */
    showClose: {
      type: Boolean,
      default: true,
    },
    /* The size of the modal */
    // eslint-disable-next-line vue/require-default-prop
    size: {
      type: String,
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
    this.opened();
    // Listen to keydown events on the document
    document.addEventListener('keydown', this.onKeyDown);
  },
  beforeUnmount() {
    // Remove the keydown event listener
    document.removeEventListener('keydown', this.onKeyDown);
  },
  methods: {
    opened() {
      this.$nextTick(() => {
        const focusableElements = 'button:not([disabled]), [href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';
        const modal = document.querySelector('.modal-content');
        const firstFocusableElement = modal.querySelectorAll(focusableElements)[0];
        const focusableContent = modal.querySelectorAll(focusableElements);
        const lastFocusableElement = focusableContent[focusableContent.length - 1];

        document.addEventListener('keydown', (e) => {
          const isTabPressed = e.key === 'Tab' || e.keyCode === 9;
          if (!isTabPressed) {
            return;
          }
          if (e.shiftKey) { // if shift key pressed for shift + tab combination
            if (document.activeElement === firstFocusableElement) {
              lastFocusableElement.focus(); // add focus for the last focusable element
              e.preventDefault();
            }
          } else if (document.activeElement === lastFocusableElement) {
            firstFocusableElement.focus();
            e.preventDefault();
          }
        });
        firstFocusableElement.focus();
      });
    },
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
