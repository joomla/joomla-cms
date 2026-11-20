<template>
  <MediaModal
    v-if="$store.state.showRenameModal"
    :size="'sm'"
    :show-close="false"
    label-element="renameTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="renameTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_RENAME') }}
      </h3>
    </template>
    <template #body>
      <div>
        <form
          class="form"
          novalidate
          @submit.prevent="save"
        >
          <div class="form-group p-3">
            <label for="name">{{ translate('COM_MEDIA_NAME') }}</label>
            <div :class="{'input-group': extension.length}">
              <input
                id="name"
                ref="nameField"
                v-model="editedName"
                class="form-control"
                type="text"
                :placeholder="translate('COM_MEDIA_NAME')"
                :disabled="isSaving"
                :class="{ 'is-invalid': renameError }"
                :aria-invalid="renameError ? 'true' : undefined"
                :aria-describedby="renameError ? 'renameFeedback' : undefined"
                required
                autocomplete="off"
              >
              <span
                v-if="extension.length"
                class="input-group-text"
              >
                {{ extension }}
              </span>
            </div>
            <div
              v-if="renameError"
              id="renameFeedback"
              class="invalid-feedback d-block"
              role="alert"
            >
              {{ renameError }}
            </div>
          </div>
        </form>
      </div>
    </template>
    <template #footer>
      <div>
        <button
          type="button"
          class="btn btn-secondary"
          :disabled="isSaving"
          @click="close()"
          @keyup.enter="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
        <button
          type="button"
          class="btn btn-success"
          :disabled="isSaving || !isValid()"
          @click="save()"
          @keyup.enter="save()"
        >
          {{ translate('JAPPLY') }}
        </button>
      </div>
    </template>
  </MediaModal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';
import MediaModal from './modal.vue';

export default {
  name: 'MediaRenameModal',
  components: {
    MediaModal,
  },
  data() {
    return {
      editedName: '',
      renameError: '',
      isSaving: false,
    };
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
    name() {
      return this.item.name.replace(`.${this.item.extension}`, '');
    },
    extension() {
      return this.item.extension;
    },
  },
  watch: {
    editedName() {
      this.renameError = '';
    },
    '$store.state.showRenameModal': {
      immediate: true,
      handler(show) {
        if (show) {
          this.editedName = this.name;
          this.renameError = '';
          this.$nextTick(() => this.$refs.nameField?.focus());
        }
      },
    },
  },
  methods: {
    /* Check if the form is valid */
    isValid() {
      return this.editedName.trim().length > 0;
    },
    /* Close the modal instance */
    close() {
      if (this.isSaving) {
        return;
      }
      this.$store.commit(types.HIDE_RENAME_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (this.isSaving || !this.isValid()) {
        // @todo mark the field as invalid
        return;
      }
      let newName = this.editedName;
      if (this.extension.length) {
        newName += `.${this.item.extension}`;
      }

      let newPath = this.item.directory;
      if (newPath.substr(-1) !== '/') {
        newPath += '/';
      }

      this.renameError = '';
      this.isSaving = true;

      // Rename the item
      return this.$store.dispatch('renameItem', {
        item: this.item,
        newPath: newPath + newName,
        newName,
      })
        .catch((error) => {
          this.renameError = this.translate('COM_MEDIA_RENAME_ERROR');
          if (error.response) {
            const response = JSON.parse(error.response);
            this.renameError = response.message || this.renameError;
          }
        })
        .finally(() => {
          this.isSaving = false;
          this.$nextTick(() => this.$refs.nameField?.focus());
        });
    },
  },
};
</script>
