<template>
    <div class="media-browser-item-directory">
        <div class="media-browser-item-preview"
             @dblclick.stop.prevent="onPreviewDblClick()">
            <div class="file-background">
                <div class="folder-icon">
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
    import navigable from "../../../mixins/navigable";

    export default {
        name: 'media-browser-item-directory',
        props: ['item'],
        mixins: [navigable],
        methods: {
            /* Handle the on preview double click event */
            onPreviewDblClick() {
                this.navigateTo(this.item.path);
            }
        }
    }
</script>