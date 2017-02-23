<template>
    <media-modal v-if="$store.state.showCreateFolderModal" :size="'sm'" @close="close()">
        <h3 slot="header">Create a new folder</h3>
        <div slot="body">
            <form class="form-horizontal">
                <div class="control-group">
                    <label class="control-label" for="folder">Folder</label>
                    <div class="controls">
                        <input type="text" id="folder" placeholder="Folder" v-model="folder">
                    </div>
                </div>
            </form>
        </div>
        <div slot="footer">
            <button class="btn btn-danger" @click="close()">Cancel</button>
            <button class="btn btn-success" @click="save()">Save</button>
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