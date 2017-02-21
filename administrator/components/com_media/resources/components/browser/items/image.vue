<template>
    <div class="media-browser-image">
        <div class="media-browser-item-preview">
            <div class="image-brackground">
                <div class="image-cropped" :style="{ backgroundImage: 'url(/images' + item.path + ')' }"></div>
            </div>
        </div>
        <div class="media-browser-item-info">
            {{ item.name }}
        </div>
    </div>
</template>

<script>
    // TODO DN get the base path and make the path dynamic
    export default {
        name: 'media-browser-item-image',
        props: ['item'],
    }
</script>