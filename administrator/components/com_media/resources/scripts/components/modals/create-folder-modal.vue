<template>
    <media-modal v-if="$store.state.showCreateFolderModal" :size="'sm'" @close="close()">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}</h3>
        <div slot="body">
            <form class="form"  @submit.prevent="save">
                <div class="form-group">
                    <label for="folder">{{ translate('COM_MEDIA_FOLDER') }}</label>
                    <input type="text" id="folder" v-focus="true" class="form-control" placeholder="Folder" v-model="folder">
                </div>
            </form>
        </div>
        <div slot="footer">
            <button class="btn btn-link" @click="close()">{{ translate('JCANCEL') }}</button>
            <button class="btn btn-success" @click="save()">{{ translate('JAPPLY') }}</button>
        </div>
    </media-modal>
</template>

<script>
    import * as types from "./../../store/mutation-types";
    import { focus } from 'vue-focus';

    export default {
        name: 'create-folder-modal',
        directives: { focus: focus },
        methods: {
            /* Close the modal instance */
            close() {
                this.$store.commit(types.HIDE_CREATE_FOLDER_MODAL);
            },
            /* Save the form and create the folder */
            save() {
                /*Checks for the folder name, if it is not declared display an error to user and abort task*/
                if(!this.folder){
                    // TODO: show an error message to user for insert a folder name
                    Joomla.renderMessages({"error":[ this.translate('JLIB_FORM_FIELD_REQUIRED_VALUE') ]});
                    return;
                }

                this.$store.dispatch('createDirectory', {
                    name: this.folder,
                    parent: this.$store.state.selectedDirectory,
                });
                this.folder = '';
            },
        },
    }
</script>
