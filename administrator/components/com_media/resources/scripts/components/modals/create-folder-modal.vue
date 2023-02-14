<template>
  <media-modal
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
              v-model.trim="folder"
              class="form-control"
              type="text"
              required
              autocomplete="off"
              v-bind:class="(isValidName()!==0 && isValid())?'is-invalid':''"
              aria-describedby="folderFeedback"
              @input="folder = $event.target.value"
            >
            <div
              v-if="isValidName()===1"
              id="folderFeedback"
              class="invalid-feedback"
            >
              {{ translate('COM_MEDIA_CREATE_NEW_FOLDER_RELATIVE_PATH_ERROR') }}
            </div>
            <div
              v-if="isValidName()===2"
              id="folderFeedback"
              class="invalid-feedback"
            >
              {{ translate('COM_MEDIA_CREATE_NEW_FOLDER_EXISTING_FOLDER_ERROR') }}
            </div>
            <div
              v-if="isValidName()===3 && isValid()"
              id="folderFeedback"
              class="invalid-feedback"
            >
              {{ translate('COM_MEDIA_CREATE_NEW_FOLDER_UNEXPECTED_CHARACTER') }}
            </div>
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
          :disabled="!isValid() || isValidName()!==0"
          @click="save()"
        >
          {{ translate('JACTION_CREATE') }}
        </button>
      </div>
    </template>
  </media-modal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';

export default {
  name: 'MediaCreateFolderModal',
  data() {
    return {
      folder: '',
    };
  },
  computed: {
    /* Get the contents of the currently selected directory */
    items() {
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const directories = this.$store.getters.getSelectedDirectoryDirectories
        .filter((dir) => dir.name.toLowerCase() === (this.folder.toLowerCase()));

      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const files = this.$store.getters.getSelectedDirectoryFiles
        .filter((file) => file.name.toLowerCase() === (this.folder.toLowerCase()));

      return [...directories, ...files];
    },
  },
  methods: {
    /* Check if the the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Check folder name is valid or not */
    isValidName() {
      if (this.folder.includes('..')) {
        return 1;
      } else if ((this.items.length !== 0)) {
        return 2;
      } else if ((!/^[\p{L}\p{N}\-_. ]+$/u.test(this.folder))) {
        return 3;
      } else {
        return 0;
      }
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
