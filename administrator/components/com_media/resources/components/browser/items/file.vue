<template>
    <div class="media-browser-item-file">
        <div class="media-browser-item-preview">
            <span class="icon material-icons">insert_drive_file</span>
        </div>
        <div class="media-browser-item-info">{{ item.name }}</div>
    </div>
</template>

<script>
    export default {
        name: 'media-browser-item-file',
        props: ['item'],
    }
</script>