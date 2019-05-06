<template>
    <media-modal v-if="$store.state.showCreateFolderModal" :size="'md'" @close="close()" label-element="createFolderTitle">
        <h3 slot="header" id="createFolderTitle" class="modal-title">{{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}</h3>
        <div slot="body">
            <form class="form" @submit.prevent="save" novalidate>
                <div class="form-group">
                    <label for="folder">{{ translate('COM_MEDIA_FOLDER_NAME') }}</label>
                    <input id="folder" class="form-control"
                           v-model.trim="folder" @input="folder = $event.target.value"
                           required autocomplete="off">
                </div>
            </form>
        </div>
        <div slot="footer">
            <button class="btn btn-secondary" @click="close()">{{ translate('JCANCEL') }}</button>
            <button class="btn btn-success" @click="save()" :disabled="!isValid()">{{ translate('JACTION_CREATE') }}
            </button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";

    export default {
        name: 'media-create-folder-modal',
        data() {
            return {
                folder: '',
            }
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
                    // TODO show an error message to user for insert a folder name
                    // TODO mark the field as invalid
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
            }
        },
    }
</script>
