<template>
    <div class="media-browser-item-file" :class="{selected: isSelected}">
        <div class="media-browser-item-preview">
            <span class="fa fa-file-text-o"></span>
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