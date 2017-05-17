<template>
    <div class="media-browser-item-directory">
        <div class="media-browser-item-preview"
             @dblclick="goTo(item.path)">
             <div class="file-background">
                <div class="folder-icon d-flex justify-content-center align-items-center">
                    <span class="fa fa-folder-o"></span>
                </div>
            </div>
        </div>
        <div class="media-browser-item-info">
            {{ item.name }}
        </div>
        <div class="media-browser-select"></div>
    </div>
</template>
<script>
    export default {
        name: 'media-browser-item-directory',
        props: ['item'],
        methods: {
            /* Go to a path */
            goTo: function (path) {
                this.$store.dispatch('getContents', path);
            },
        }
    }
</script>