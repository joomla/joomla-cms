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
                class="form-control"
                type="text"
                :placeholder="translate('COM_MEDIA_NAME')"
                :value="name"
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
          </div>
        </form>
      </div>
    </template>
    <template #footer>
      <div>
        <button
          type="button"
          class="btn btn-secondary"
          @click="close()"
          @keyup.enter="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
        <button
          type="button"
          class="btn btn-success"
          :disabled="!isValid()"
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
  updated() {
    this.$nextTick(() => (this.$refs.nameField ? this.$refs.nameField.focus() : null));
  },
  methods: {
    /* Check if the form is valid */
    isValid() {
      return this.item.name.length > 0;
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(types.HIDE_RENAME_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo mark the field as invalid
        return;
      }
      let newName = this.$refs.nameField.value;
      if (this.extension.length) {
        newName += `.${this.item.extension}`;
      }

      let newPath = this.item.directory;
      if (newPath.substr(-1) !== '/') {
        newPath += '/';
      }

      // Rename the item
      this.$store.dispatch('renameItem', {
        item: this.item,
        newPath: newPath + newName,
        newName,
      });
    },
  },
};
</script>
