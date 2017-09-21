import Directory from "./directory.vue";
import File from "./file.vue";
import Image from "./image.vue";
import Row from "./row.vue";
import * as types from "./../../../store/mutation-types";

export default {
    functional: true,
    props: ['item'],
    render: function (createElement, context) {

        const store = context.parent.$store;
        const item = context.props.item;

        /**
         * Return the correct item type component
         */
        function itemType() {
            if (store.state.listView == 'table') {
                return Row;
            }

            let imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

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
	     * Create and dispatch onMediaFileSelected Event
	     *
	     * @param {object}  data  The data for the detail
	     *
	     * @returns {void}
	     */
        function sendEvent(data) {
	        const ev = new CustomEvent('onMediaFileSelected', {"bubbles":true, "cancelable":false, "detail": data});
	        window.parent.document.dispatchEvent(ev);
        }
        /**
         * Handle the click event
         * @param event
         */
        function handleClick(event) {
	        let path = false;
	        const data = {
		        path: path,
		        thumb: false,
		        fileType: item.mime_type ? item.mime_type : false,
		        extension: item.extension ? item.extension : false,
	        };

            if (item.type === 'file') {
	            const csrf = Joomla.getOptions('com_media').csrfToken;
	            const apiBaseUrl = Joomla.getOptions('com_media').apiBaseUrl;
	            Joomla.request({
		            url: `${apiBaseUrl}&task=api.files&url=true&path=${item.path}&${csrf}=1`,
		            method: 'GET',
		            perform: true,
		            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		            onSuccess: (response) => {
			            const resp = JSON.parse(response);
			            if (resp.success === true) {
				            if (resp.data[0].url) {
					            if (/local-/.test(item.path)) {
						            const server = Joomla.getOptions('system.paths').rootFull;
						            const newPath = resp.data[0].url.split(server)[1];

						            data.path  = newPath;
						            if (resp.data[0]['thumb_path'])
						            data.thumb = resp.data[0].thumb_path;
					            } else {
						            data.path  = path;
						            if (resp.data[0]['thumb_path'])
						            data.thumb = resp.data[0].thumb_path;
					            }
				            }
			            }
			            sendEvent(data)
		            },
		            onError: () => {
			            sendEvent(data)
		            },
	            });
            }

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
            if (store.state.selectedItems.length > 1) {
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
            },
            [
                createElement(itemType(), {
                    props: context.props,
                })
            ]
        );
    }
}