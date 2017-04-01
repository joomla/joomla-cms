<template>
    <div class="media-browser-image">
        <div class="media-browser-item-preview">
            <div class="image-brackground">
                <div class="image-cropped" :style="{ backgroundImage: 'url(' + itemUrl + ')' }"
                 v-on:dblclick="openEditView()"></div>
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
        computed: {
            /* Get the item url */
            itemUrl() {
                // TODO should we use relative urls here?
                const fileBaseUrl = Joomla.getOptions('com_media').fileBaseUrl || '/images';

                return fileBaseUrl + this.item.path;
            },
            openEditView() {
                // TODO should we use relative urls here?
                const fileBaseUrl = Joomla.getOptions('com_media').editViewUrl + '&path=';

                window.location.href = fileBaseUrl + this.item.path;
            }
        }
    }
</script>