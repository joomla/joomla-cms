<template>
    <div class="media-toolbar">
        <div class="media-toolbar-create">
            <div class="btn-group">
                <button class="btn btn-sm btn-success">
                    <span class="icon-apply icon-white"></span> New
                </button>
                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                <ul class="dropdown-menu">
                    <li class="dropdown-item">
                        <a href="#" @click.prevent="showCreateFolderModal()">Create Folder</a>
                    </li>
                    <li class="divider"></li>
                    <li class="dropdown-item"><a href="#">Upload File</a></li>
                    <li class="dropdown-item"><a href="#">Upload Folder</a></li>
                </ul>
            </div>
        </div>
        <media-breadcrumb></media-breadcrumb>
        <ul class="media-tools">
            <li>
                <a href="#"><span class="fa fa-list"></span></a>
            </li>
            <li>
                <a href="#"><span class="fa fa-info-circle"></span></a>
            </li>
            <li>
                <a href="#"><span class="fa fa-question-circle"></span></a>
            </li>
            <li>
                <a href="#"><span class="fa fa-cog"></span></a>
            </li>
        </ul>
    </div>
</template>

<script>
    import * as types from "./../../store/mutation-types";
    export default {
        name: 'media-toolbar',
        methods: {
            /* Close the modal instance */
            showCreateFolderModal() {
                this.$store.commit(types.SHOW_CREATE_FOLDER_MODAL);
            },
            /* Handle keydown events */
            onKeyDown(event) {
                if (this.show && event.keyCode == 27) {
                    this.close();
                }
            }
        },
    }
</script>