<template>
    <div class="media-browser-image" @dblclick="openPreview()" @mouseleave="showActions = false">
        <div class="media-browser-item-preview">
            <div class="image-brackground">
                <div class="image-cropped" :style="{ backgroundImage: 'url(' + thumbUrl + ')' }"></div>
            </div>
        </div>
        <div class="media-browser-item-info">
            {{ item.name }} {{ item.filetype }}
        </div>
        <a href="#" class="media-browser-select"
          @click.stop="toggleSelect()"
          :aria-label="translate('COM_MEDIA_TOGGLE_SELECT_ITEM')">
        </a>
        <div class="media-browser-actions" :class="{'active': showActions}">
            <a href="#" class="action-toggle"
              :aria-label="translate('COM_MEDIA_OPEN_ITEM_ACTIONS')">
                <span class="image-browser-action fa fa-ellipsis-h" aria-hidden="true"
                      @click.stop="showActions = true"></span>
            </a>
            <div class="media-browser-actions-list">
                <a href="#" class="action-preview"
                  :aria-label="translate('COM_MEDIA_ACTION_PREVIEW')">
                    <span class="image-browser-action fa fa-search-plus" aria-hidden="true"
                          @click.stop="openPreview()"></span>
                </a>
                <a href="#" class="action-download"
                  :aria-label="translate('COM_MEDIA_ACTION_DOWNLOAD')">
                    <span class="image-browser-action fa fa-download" aria-hidden="true"
                          @click.stop="download()"></span>
                </a>
                <a href="#" class="action-rename"
                  :aria-label="translate('COM_MEDIA_ACTIN_RENAME')">
                    <span class="image-browser-action fa fa-text-width" aria-hidden="true"
                          @click.stop="openRenameModal()"></span>
                </a>
                <a href="#" class="action-edit"
                  v-if="canEdit"
                    :aria-label="translate('COM_MEDIA_ACTION_EDIT')">
                    <span class="image-browser-action fa fa-pencil" aria-hidden="true" @click.stop="editItem()"></span>
                </a>
                <a href="#" class="action-delete"
                  :aria-label="translate('COM_MEDIA_ACTION_DELETE')">
                    <span class="image-browser-action fa fa-trash" aria-hidden="true" @click.stop="deleteItem()"></span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import * as types from './../../../store/mutation-types';

    export default {
        name: 'media-browser-item-image',
        data() {
            return {
                showActions: false,
            }
        },
        props: ['item'],
        computed: {
            /* Get the item url */
            thumbUrl() {
                return this.item.thumb_path;
            },
            /* Check if the item is an image to edit */
            canEdit() {
                return ['jpg', 'jpeg', 'png'].indexOf(this.item.extension.toLowerCase()) > -1;
            }
        },
        methods: {
            /* Preview an item */
            openPreview() {
                this.$store.commit(types.SHOW_PREVIEW_MODAL);
                this.$store.dispatch('getFullContents', this.item);
            },
            /* Preview an item */
            download() {
                this.$store.dispatch('download', this.item);
            },
            /* Delete an item */
            deleteItem() {
                this.$store.dispatch('deleteItem', this.item);
            },
            /* Rename an item */
            openRenameModal() {
                this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
                this.$store.commit(types.SHOW_RENAME_MODAL);
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
            },
        }
    }
</script>