<template>
  <MediaModal
    v-if="$store.state.showShareModal"
    :size="'md'"
    :show-close="false"
    label-element="shareTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="shareTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_SHARE') }}
      </h3>
    </template>
    <template #body>
      <div class="p-3">
        <div class="desc">
          {{ translate('COM_MEDIA_SHARE_DESC') }}

          <template v-if="!url">
            <div class="control">
              <button
                class="btn btn-success w-100"
                type="button"
                @click="generateUrl"
              >
                {{ translate('COM_MEDIA_ACTION_SHARE') }}
              </button>
            </div>
          </template>
          <template v-else>
            <div class="control">
              <span class="input-group">
                <input
                  id="url"
                  ref="urlText"
                  v-model="url"
                  readonly
                  type="url"
                  class="form-control input-xxlarge"
                  placeholder="URL"
                  autocomplete="off"
                >
                <button
                  class="btn btn-secondary"
                  type="button"
                  :title="translate('COM_MEDIA_SHARE_COPY')"
                  @click="copyToClipboard"
                >
                  <span
                    class="icon-clipboard"
                    aria-hidden="true"
                  />
                </button>
              </span>
            </div>
          </template>
        </div>
      </div>
    </template>
    <template #footer>
      <div>
        <button
          class="btn btn-secondary"
          @click="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
      </div>
    </template>
  </MediaModal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import translate from '../../plugins/translate.es6';
import MediaModal from './modal.vue';

export default {
  name: 'MediaShareModal',
  components: {
    MediaModal,
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },

    url() {
      return (this.$store.state.previewItem && Object.prototype.hasOwnProperty.call(this.$store.state.previewItem, 'url') ? this.$store.state.previewItem.url : null);
    },
  },
  methods: {
    /* Close the modal instance and reset the form */
    close() {
      this.$store.commit(types.HIDE_SHARE_MODAL);
      this.$store.commit(types.LOAD_FULL_CONTENTS_SUCCESS, null);
    },

    // Generate the url from backend
    generateUrl() {
      this.$store.dispatch('getFullContents', this.item);
    },

    // Copy to clipboard
    copyToClipboard() {
      this.$refs.urlText.focus();
      this.$refs.urlText.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        // @todo Error handling in joomla way
        window.alert(translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
      }
    },
  },
};
</script>
