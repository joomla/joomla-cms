<template>
    <div class="media-browser-item-file" :class="{selected: isSelected}">
        <div class="media-browser-item-preview">
            <div class="file-background">
                <div class="file-icon d-flex justify-content-center align-items-center">
                    <span class="fa fa-file-text-o"></span>
                </div>
            </div>
        </div>
        <div class="media-browser-item-info">{{ item.name }}</div>
        <div class="media-browser-select"></div>
    </div>
</template>

<script>
    export default {
        name: 'media-browser-item-file',
        props: ['item'],
    }
</script> 