<template>
    <div class="media-browser-image">
        <div class="media-browser-item-preview">
            <div class="image-brackground">
                <div class="image-cropped" :style="{ backgroundImage: 'url(' + itemUrl + ')' }"
                 @dblclick="openEditView()"></div>
            </div>
        </div>
        <div class="media-browser-item-info">
            {{ item.name }} {{ item.filetype }}
        </div>
        <div class="media-browser-select" @click.stop="toggleSelect()"></div>
        <div class="media-browser-actions d-flex">
            <a href="#" class="action-delete">
                <span class="image-browser-action fa fa-trash" aria-hidden="true" @click.stop="deleteItem()"></span>
            </a>
            <a href="#" class="action-edit">
                <span class="image-browser-action fa fa-pencil" aria-hidden="true" @click.stop="editItem()"></span>
            </a>
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
            }
        },
        methods: {
            /* Delete am item */
            deleteItem() {
                this.$store.dispatch('deleteItem', this.item);
            },
            /* Edit an item */
            editItem() {
              // TODO should we use relative urls here?
              const fileBaseUrl = Joomla.getOptions('com_media').editViewUrl + '&path=';

              window.location.href = fileBaseUrl + this.item.path;
            },
            /* Toggle the item selection */
            toggleSelect() {
                this.$store.dispatch('toggleBrowserItemSelect', this.item);
            }
        }
    }
</script>