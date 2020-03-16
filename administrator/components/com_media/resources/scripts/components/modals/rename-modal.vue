<template>
    <media-modal v-if="$store.state.showRenameModal" :size="'sm'" @close="close()" :show-close="false" label-element="renameTitle">
        <h3 slot="header" id="renameTitle" class="modal-title">{{ translate('COM_MEDIA_RENAME') }}</h3>
        <div slot="body">
            <form class="form" @submit.prevent="save" novalidate>
                <div class="form-group">
                    <label for="name">{{ translate('COM_MEDIA_NAME') }}</label>
                    <div :class="{'input-group': extension.length}">
                        <input id="name" class="form-control" :placeholder="translate('COM_MEDIA_NAME')"
                               :value="name" required autocomplete="off" ref="nameField">
                        <span class="input-group-addon" v-if="extension.length">{{extension }}</span>
                    </div>
                </div>
            </form>
        </div>
        <div slot="footer">
            <button type="button" class="btn btn-secondary" @click="close()" @keyup.enter="close()">{{ translate('JCANCEL') }}</button>
            <button type="button" class="btn btn-success" @click="save()" @keyup.enter="save()" :disabled="!isValid()">{{ translate('JAPPLY') }}
            </button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";

    export default {
        name: 'media-rename-modal',
        computed: {
            item() {
                return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
            },
            name() {
                return this.item.name.replace('.' + this.item.extension, '');
            },
            extension() {
                return this.item.extension;
            }
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
                    // TODO mark the field as invalid
                    return;
                }
                let newName = this.$refs.nameField.value;
                if (this.extension.length) {
                    newName += '.' + this.item.extension;
                }

                let newPath = this.item.directory;
                if (newPath.substr(-1) !== '/') {
                    newPath += '/';
                }

                // Rename the item
                this.$store.dispatch('renameItem', {
                    path: this.item.path,
                    newPath: newPath + newName,
                    newName: newName,
                });
            },
        }
    }
</script>
