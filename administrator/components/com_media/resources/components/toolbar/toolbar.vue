<template>
    <div class="media-toolbar">
        <div class="create-wrapper">
            <div class="btn-group">
                <button class="btn btn-success">Upload</button>
                <button class="btn dropdown-toggle btn-success" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#">Create Folder</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Upload File</a></li>
                    <li><a href="#">Upload Folder</a></li>
                </ul>
            </div>
        </div>
        <media-breadcrumb></media-breadcrumb>
        <div class="media-tools">
            <a href="#" class="material-icons">list</a>
            <a href="#" class="material-icons">info_outline</a>
            <a href="#" class="material-icons">help_outline</a>
            <a href="#" class="material-icons">settings</a>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'media-toolbar',
    }
</script>