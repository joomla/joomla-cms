<template>
    <media-modal v-if="$store.state.showCreateFolderModal" :size="'sm'" @close="close()">
        <h3 slot="header" class="modal-title">{{ translate('COM_MEDIA_CREATE_NEW_FOLDER') }}</h3>
        <div slot="body">
            <form class="form">
                <div class="form-group">
                    <label for="folder">{{ translate('COM_MEDIA_FOLDER') }}</label>
                    <input type="text" id="folder" class="form-control" placeholder="Folder" v-model="folder">
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
    export default {
        name: 'create-folder-modal',
        methods: {
            /* Close the modal instance */
            close() {
                this.$store.commit(types.HIDE_CREATE_FOLDER_MODAL);
            },
            /* Save the form and create the folder */
            save() {
                this.$store.dispatch('createDirectory', {
                    name: this.folder,
                    parent: this.$store.state.selectedDirectory,
                });
                this.folder = '';
            }
        }
    }
</script>