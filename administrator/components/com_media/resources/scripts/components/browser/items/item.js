import Directory from "./directory.vue";
import File from "./file.vue";
import Image from "./image.vue";
import * as types from "./../../../store/mutation-types";

export default {
    functional: true,
    props: ['item'],
    render: function (createElement, context) {

        const store = context.parent.$store;
        const selectedItems = store.state.selectedItems;
        const item = context.props.item;

        /**
         * Return the correct item type component
         */
        function itemType() {
            let imageExtensions = ['jpg', 'png', 'gif'];

            // Render directory items
            if (item.type === 'dir') return Directory;

            // Render image items
            if (item.extension && imageExtensions.indexOf(item.extension.toLowerCase()) !== -1) {
                return Image;
            }

            // Default to file type
            return File;
        }

        /**
         * Whether or not the item is currently selected
         * @returns {boolean}
         */
        function isSelected() {
            return store.state.selectedItems.some(selected => selected.path === item.path);
        }

        /**
         * Handle the click event
         * @param event
         */
        function handleClick(event) {
            // Handle clicks when the item was not selected
            if (!isSelected()) {
                // Unselect all other selected items, if the shift key was not pressed during the click event
                if (!(event.shiftKey || event.keyCode === 13)) {
                    store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                }
                store.commit(types.SELECT_BROWSER_ITEM, item);
                return;
            }

            // If more than one item was selected and the user clicks again on the selected item,
            // he most probably wants to unselect all other items.
            if (selectedItems.length > 1) {
                store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                store.commit(types.SELECT_BROWSER_ITEM, item);
            }
        }

        return createElement('div', {
                'class': {
                    'media-browser-item': true,
                    selected: isSelected(),
                },
                on: {
                    click: handleClick,
                }
            }, [createElement(itemType(), {
                props: context.props,
            })
            ]
        );
    }
}
