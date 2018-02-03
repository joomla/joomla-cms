<template>
    <div class="media-browser-image" @dblclick="openPreview()">
        <div class="media-browser-item-preview">
            <div class="file-background">
                <div class="file-icon">
                    <span class="fa fa-file-text-o"></span>
                </div>
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
        name: 'media-browser-item-video',
        data() {
            return {
                showActions: false,
            }
        },
        props: ['item'],
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
            /* Toggle the item selection */
            toggleSelect() {
                this.$store.dispatch('toggleBrowserItemSelect', this.item);
            },
        }
    }
</script>
