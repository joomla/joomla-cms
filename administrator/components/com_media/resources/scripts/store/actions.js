import {api} from "../app/Api";
import * as types from "./mutation-types";
import translate from "../plugins/translate";
import {notifications} from "../app/Notifications";
import * as FileSaver from './../../../node_modules/file-saver/FileSaver';

// Actions are similar to mutations, the difference being that:
// - Instead of mutating the state, actions commit mutations.
// - Actions can contain arbitrary asynchronous operations.

// TODO move to utils
function updateUrlPath(path) {
    if (path == null) {
        path = '';
    }
    let url = window.location.href;
    var pattern = new RegExp('\\b(path=).*?(&|$)');

    if (url.search(pattern) >= 0) {
        history.pushState(null, '', url.replace(pattern, '$1' + path + '$2'));
    } else {
        history.pushState(null, '', url + (url.indexOf('?') > 0 ? '&' : '?') + 'path=' + path);
    }
}

/**
 * Get contents of a directory from the api
 * @param commit
 * @param payload
 */
export const getContents = (context, payload) => {

    // Update the url
    updateUrlPath(payload);
    context.commit(types.SET_IS_LOADING, true);

    api.getContents(payload, 0)
        .then(contents => {
            context.commit(types.LOAD_CONTENTS_SUCCESS, contents);
            context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
            context.commit(types.SELECT_DIRECTORY, payload);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            // TODO error handling
            context.commit(types.SET_IS_LOADING, false);
            console.log("error", error);
        });
}

/**
 * Get the full contents of a directory
 * @param context
 * @param payload
 */
export const getFullContents = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    api.getContents(payload.path, 1)
        .then(contents => {
            context.commit(types.LOAD_FULL_CONTENTS_SUCCESS, contents.files[0]);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            // TODO error handling
            context.commit(types.SET_IS_LOADING, false);
            console.log("error", error);
        });
}

/**
 * Download a file
 * @param context
 * @param payload
 */
export const download = (context, payload) => {
    api.getContents(payload.path, 0, 1)
        .then(contents => {
            var file = contents.files[0];

            // Converte the base 64 encoded string to a blob
	        var byteCharacters = atob(file.content);
	        var byteArrays = [];

	        for (var offset = 0; offset < byteCharacters.length; offset += 512) {
		        var slice = byteCharacters.slice(offset, offset + 512);

		        var byteNumbers = new Array(slice.length);
		        for (var i = 0; i < slice.length; i++) {
			        byteNumbers[i] = slice.charCodeAt(i);
		        }

		        var byteArray = new Uint8Array(byteNumbers);

		        byteArrays.push(byteArray);
	        }

	        // Open the save as file dialog
	        FileSaver.saveAs(new Blob(byteArrays, {type: file.mime_type}), file.name);
        })
        .catch(error => {
            console.log("error", error);
        });
}

/**
 * Toggle the selection state of an item
 * @param commit
 * @param payload
 */
export const toggleBrowserItemSelect = (context, payload) => {
    const item = payload;
    const isSelected = context.state.selectedItems.some(selected => selected.path === item.path);
    if (!isSelected) {
        context.commit(types.SELECT_BROWSER_ITEM, item);
    } else {
        context.commit(types.UNSELECT_BROWSER_ITEM, item);
    }
}

/**
 * Create a new folder
 * @param commit
 * @param payload object with the new folder name and its parent directory
 */
export const createDirectory = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    api.createDirectory(payload.name, payload.parent)
        .then(folder => {
            context.commit(types.CREATE_DIRECTORY_SUCCESS, folder);
            context.commit(types.HIDE_CREATE_FOLDER_MODAL);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            // TODO error handling
            context.commit(types.SET_IS_LOADING, false);
            console.log("error", error);
        })
}

/**
 * Create a new folder
 * @param commit
 * @param payload object with the new folder name and its parent directory
 */
export const uploadFile = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    api.upload(payload.name, payload.parent, payload.content, payload.override || false)
        .then(file => {
            context.commit(types.UPLOAD_SUCCESS, file);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            context.commit(types.SET_IS_LOADING, false);

            // Handle file exists
            if (error.status === 409) {
                if (notifications.ask(translate.sprintf('COM_MEDIA_FILE_EXISTS_AND_OVERRIDE', payload.name), {})) {
                    payload.override = true;
                    uploadFile(context, payload);
                }
            }
        })
}

/**
 * Delete a single item
 * @param context
 * @param payload object: the item to delete
 */
export const deleteItem = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    const item = payload;
    api.delete(item.path)
        .then(() => {
            context.commit(types.DELETE_SUCCESS, item);
            context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            // TODO error handling
            context.commit(types.SET_IS_LOADING, false);
            console.log("error", error);
        })
}

/**
 * Rename an item
 * @param context
 * @param payload object: the old and the new path
 */
export const renameItem = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    api.rename(payload.path, payload.newPath)
        .then((item) => {
            context.commit(types.RENAME_SUCCESS, {
                item: item,
                oldPath: payload.path,
            });
            context.commit(types.HIDE_RENAME_MODAL);
            context.commit(types.SET_IS_LOADING, false);
        })
        .catch(error => {
            // TODO error handling
            context.commit(types.SET_IS_LOADING, false);
            console.log("error", error);
        })
}

/**
 * Delete the selected items
 * @param context
 * @param payload object
 */
export const deleteSelectedItems = (context, payload) => {
    context.commit(types.SET_IS_LOADING, true);
    // Get the selected items from the store
    const selectedItems = context.state.selectedItems;
    if (selectedItems.length > 0) {
        selectedItems.forEach(item => {
            api.delete(item.path)
                .then(() => {
                    context.commit(types.DELETE_SUCCESS, item);
                    context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                    context.commit(types.SET_IS_LOADING, false);
                })
                .catch(error => {
                    // TODO error handling
                    context.commit(types.SET_IS_LOADING, false);
                    console.log("error", error);
                })
        })
    } else {
        // TODO notify the user that he has to select at least one item
    }
}
