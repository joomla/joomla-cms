import {api} from "../app/Api";
import * as types from "./mutation-types";

// Actions are similar to mutations, the difference being that:
// - Instead of mutating the state, actions commit mutations.
// - Actions can contain arbitrary asynchronous operations.

/**
 * Get contents of a directory from the api
 * @param commit
 * @param payload
 */
export const getContents = (context, payload) => {
    api.getContents(payload)
        .then(contents => {
            context.commit(types.LOAD_CONTENTS_SUCCESS, contents);
            context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
            context.commit(types.SELECT_DIRECTORY, payload);
        })
        .catch(error => {
            // TODO error handling
            console.log("error", error);
        });
}

/**
 * Create a new folder
 * @param commit
 * @param payload object with the new folder name and its parent directory
 */
export const createDirectory = (context, payload) => {
    api.createDirectory(payload.name, payload.parent)
        .then(folder => {
            context.commit(types.CREATE_DIRECTORY_SUCCESS, folder);
            context.commit(types.HIDE_CREATE_FOLDER_MODAL);
        })
        .catch(error => {
            // TODO error handling
            console.log("error", error);
        })
}

/**
 * Create a new folder
 * @param commit
 * @param payload object with the new folder name and its parent directory
 */
export const uploadFile = (context, payload) => {
    api.upload(payload.name, payload.parent, payload.content)
        .then(file => {
            context.commit(types.UPLOAD_SUCCESS, file);
        })
        .catch(error => {
            // TODO error handling
            console.log("error", error);
        })
}

/**
 * Delete the selected items
 * @param context
 * @param payload object with the new folder name and its parent directory
 */
export const deleteSelectedItems = (context, payload) => {
    // Get the selected items from the store
    const selectedItems = context.state.selectedItems;
    if (selectedItems.length > 0) {
        selectedItems.forEach(item => {
            api.delete(item.path)
                .then(() => {
                    context.commit(types.DELETE_SUCCESS, item);
                    context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                })
                .catch(error => {
                    // TODO error handling
                    console.log("error", error);
                })
        })
    } else {
        // TODO notify the user that he has to select at least one item
    }
}

