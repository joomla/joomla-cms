<template>
    <div class="media-browser-item-directory"
         @click="select(item)"
         @dblclick="goTo(item.path)">
        <div class="media-browser-item-preview">
            <span class="fa fa-folder"></span>
        </div>
        <div class="media-browser-item-info">
            {{ item.name }}
        </div>
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
            /* Select the directory */
            select: function(item) {
                // TODO implement
            }
        }
    }
</script>