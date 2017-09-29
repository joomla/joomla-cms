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
    import navigable from "../../../mixins/navigable";
    import * as types from './../../../store/mutation-types';

    export default {
        name: 'media-browser-item-directory',
        data() {
            return {
                showActions: false,
            }
        },
        props: ['item'],
        mixins: [navigable],
        methods: {
            /* Handle the on preview double click event */
            onPreviewDblClick() {
                this.navigateTo(this.item.path);
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
