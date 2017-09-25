<template>
    <div class="media-browser-image" @dblclick="openPreview()">
        <div class="media-browser-item-preview">
            <div class="file-background">
                <div class="file-icon">
                    <span class="fa fa-file-text-o"></span>
                </div>
            </div>
        </div>
        <div class="media-browser-item-info">{{ item.name }}</div>
        <div class="media-browser-select"></div>

        <div class="media-browser-select" @click.stop="toggleSelect()"></div>
        <div class="media-browser-actions d-flex">
            <a href="#" class="action-delete">
                <span class="image-browser-action fa fa-trash" aria-hidden="true" @click.stop="deleteItem()"></span>
            </a>
        </div>
    </div>
</template>

<script>
    import * as types from './../../../store/mutation-types';

    export default {
        name: 'media-browser-item-video',
        props: ['item'],
        methods: {
            /* Preview an item */
            openPreview() {
                this.$store.commit(types.SHOW_PREVIEW_MODAL);
                this.$store.dispatch('getFullContents', this.item);
            },
            /* Delete an item */
            deleteItem() {
                this.$store.dispatch('deleteItem', this.item);
            },
            /* Toggle the item selection */
            toggleSelect() {
                this.$store.dispatch('toggleBrowserItemSelect', this.item);
            }
        }
    }
</script>
