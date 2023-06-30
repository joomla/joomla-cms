<template>
  <MediaModal
    v-if="$store.state.showCreateFolderModal"
    :size="'md'"
    label-element="createFolderTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="createFolderTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}
      </h3>
    </template>
    <template #body>
      <div class="p-3">
        <form
          class="form"
          novalidate
          @submit.prevent="save"
        >
          <div class="form-group">
            <label for="folder">{{ translate('COM_MEDIA_FOLDER_NAME') }}</label>
            <input
              id="folder"
              ref="input"
              v-model.trim="folder"
              class="form-control"
              type="text"
              required
              autocomplete="off"
              @input="folder = $event.target.value"
            >
          </div>
        </form>
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
        <button
          class="btn btn-success"
          :disabled="!isValid()"
          @click="save()"
        >
          {{ translate('JACTION_CREATE') }}
        </button>
      </div>
    </template>
  </MediaModal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import MediaModal from './modal.vue';

export default {
  name: 'MediaCreateFolderModal',
  components: {
    MediaModal,
  },
  data() {
    return {
      folder: '',
    };
  },
  watch: {
    '$store.state.showCreateFolderModal': function (show) {
      this.$nextTick(() => {
        if (show && this.$refs.input) {
          this.$refs.input.focus();
        }
      });
    },
  },
  methods: {
    /* Check if the the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Close the modal instance */
    close() {
      this.reset();
      this.$store.commit(types.HIDE_CREATE_FOLDER_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo show an error message to user for insert a folder name
        // @todo mark the field as invalid
        return;
      }

      // Create the directory
      this.$store.dispatch('createDirectory', {
        name: this.folder,
        parent: this.$store.state.selectedDirectory,
      });
      this.reset();
    },
    /* Reset the form */
    reset() {
      this.folder = '';
    },
  },
};
</script>
