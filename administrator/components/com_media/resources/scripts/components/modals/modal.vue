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
    /* Listen to keydown events on the document */
    this.focusableElements = 'button:not([disabled]), [href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';
    this.modal = document.querySelector('.modal-dialog');
    this.focusableContent = this.modal.querySelectorAll(this.focusableElements);
    this.firstFocusableElement = this.focusableContent[0];
    this.lastFocusableElement = this.focusableContent[this.focusableContent.length - 1];
    document.addEventListener('keydown', this.onKeyPress);
    this.firstFocusableElement.focus();

    /* Setting up the MutationObserver on the modal-footer */
    this.targetNode = document.querySelector('.modal-footer');
    this.config = { attributes: true, childList: true, subtree: true };
    this.observer = new MutationObserver(this.callBack);
    this.observer.observe(this.targetNode, this.config);
  },
  beforeUnmount() {
    /* Disconnect the mutation observer */
    this.observer.disconnect();

    /* Remove the keydown event listener */
    document.removeEventListener('keydown', this.onKeyPress);
  },
  methods: {
    /* Callback function, to be executed when changes in the DOM are observed */
    callBack(mutationsList) {
      mutationsList.forEach((mutation) => {
        if (mutation.type === 'attributes') {
          document.removeEventListener('keydown', this.onKeyPress);
          this.focusableContent = this.modal.querySelectorAll(this.focusableElements);
          this.firstFocusableElement = this.focusableContent[0];
          this.lastFocusableElement = this.focusableContent[this.focusableContent.length - 1];
          document.addEventListener('keydown', this.onKeyPress);
        }
      });
    },
    /* Handle KeyDown events */
    onKeyPress(e) {
      const isTabPressed = e.key === 'Tab' || e.keyCode === 9;
      if (!isTabPressed) {
        return;
      }
      if (e.keyCode === 27 || e.key === 'Escape') {
        this.close();
      }
      if (e.shiftKey) { // if shift key pressed for shift + tab combination
        if (document.activeElement === this.firstFocusableElement) {
          this.lastFocusableElement.focus(); // add focus for the last focusable element
          e.preventDefault();
        }
      } else if (document.activeElement === this.lastFocusableElement) {
        this.firstFocusableElement.focus();
        e.preventDefault();
      }
    },
    /* Close the modal instance */
    close() {
      this.$emit('close');
    },
  },
};
</script>
