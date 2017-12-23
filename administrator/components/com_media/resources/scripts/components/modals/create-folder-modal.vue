<template>
    <media-modal v-if="$store.state.showCreateFolderModal" :size="'sm'" @close="close()">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}</h3>
        <div slot="body">
            <form class="form" @submit.prevent="save" novalidate>
                <div class="form-group">
                    <label for="folder">{{ translate('COM_MEDIA_FOLDER') }}</label>
                    <input type="text" id="folder" class="form-control" placeholder="Folder"
                           v-focus="true" v-model.trim="folder" @input="folder = $event.target.value"
                           required autocomplete="off">
                </div>
            </form>
        </div>
        <div slot="footer">
            <button class="btn btn-link" @click="close()">{{ translate('JCANCEL') }}</button>
            <button class="btn btn-success" @click="save()" :disabled="!isValid()">{{ translate('JAPPLY') }}</button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";
    import {focus} from 'vue-focus';

    export default {
        name: 'create-folder-modal',
        directives: {focus: focus},
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
                    Joomla.renderMessages({"error": [this.translate('JLIB_FORM_FIELD_REQUIRED_VALUE')]});
                    return;
                }

                // Create the directory
                this.$store.dispatch('createDirectory', {
                    name: this.folder,
                    parent: this.$store.state.selectedDirectory,
                });

                // Reset the form
                this.reset();
            },
            /* Reset the form */
            reset() {
                this.folder = '';
            }
        },
    }
</script>
