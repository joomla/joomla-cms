<template>
    <tr @dblclick.stop.prevent="onDblClick()" @click="onClick" :class="{selected: this.isSelected()}">
        <td class="type" :data-type="item.extension">
        </td>
        <th scope="row" class="name">
            {{ item.name }}
        </th>
        <td class="size">
            {{ item.size }}
        </td>
        <td class="dimension">
            {{ dimension }}
        </td>
        <td class="created">
            {{ item.create_date_formatted }}
        </td>
        <td class="modified">
            {{ item.modified_date_formatted }}
        </td>
    </tr>
</template>

<script>
    import * as types from './../../../store/mutation-types';
    import navigable from "../../../mixins/navigable";

    export default {
        name: 'media-browser-item-row',
        props: ['item'],
        mixins: [navigable],
        computed: {
            /* The dimension of a file */
            dimension() {
                if (!this.item.width) {
                    return '';
                }
                return `${this.item.width} x ${this.item.height}`;
            },
            isDir() {
                return (this.item.type === 'dir');
            }
        },

        methods: {
            /* Handle the on row double click event */
            onDblClick() {
                if (this.isDir) {
                    this.navigateTo(this.item.path);
                    return;
                }

                let extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];

                // Show preview
                if (this.item.extension && extensionWithPreview.indexOf(this.item.extension.toLowerCase()) !== -1) {
                    this.$store.commit(types.SHOW_PREVIEW_MODAL);
                    this.$store.dispatch('getFullContents', this.item);
                }
            },

            /**
             * Whether or not the item is currently selected
             * @returns {boolean}
             */
            isSelected() {
                return this.$store.state.selectedItems.some(selected => selected.path === this.item.path);
            },


            /**
             * Handle the click event
             * @param event
             */
            onClick(event) {
                let path = false;
                const data = {
                    path: path,
                    thumb: false,
                    fileType: this.item.mime_type ? this.item.mime_type : false,
                    extension: this.item.extension ? this.item.extension : false,
                };

                if (this.item.type === 'file') {
                    data.path = this.item.path;
                    data.thumb = this.item.thumb ? this.item.thumb : false;

                    const ev = new CustomEvent('onMediaFileSelected', {
                        "bubbles": true,
                        "cancelable": false,
                        "detail": data
                    });
                    window.parent.document.dispatchEvent(ev);
                }

                // Handle clicks when the item was not selected
                if (!this.isSelected()) {
                    // Unselect all other selected items, if the shift key was not pressed during the click event
                    if (!(event.shiftKey || event.keyCode === 13)) {
                        this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                    }
                    this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
                    return;
                }

                // If more than one item was selected and the user clicks again on the selected item,
                // he most probably wants to unselect all other items.
                if (this.$store.state.selectedItems.length > 1) {
                    this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                    this.$store.commit(types.SELECT_BROWSER_ITEM, this.item);
                }
            },

        }
    }
</script>
