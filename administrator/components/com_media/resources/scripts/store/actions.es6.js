import { api } from '../app/Api.es6';
import * as types from './mutation-types.es6';
import translate from '../plugins/translate.es6';
import { notifications } from '../app/Notifications.es6';

const updateUrlPath = (path) => {
  const currentPath = path === null ? '' : path;
  const url = new URL(window.location.href);

  if (url.searchParams.has('path')) {
    window.history.pushState(null, '', url.href.replace(/\b(path=).*?(&|$)/, `$1${currentPath}$2`));
  } else {
    window.history.pushState(null, '', `${url.href + (url.href.indexOf('?') > 0 ? '&' : '?')}path=${currentPath}`);
  }
};

/**
 * Actions are similar to mutations, the difference being that:
 * Instead of mutating the state, actions commit mutations.
 * Actions can contain arbitrary asynchronous operations.
 */

/**
 * Get contents of a directory from the api
 * @param context
 * @param payload
 */
export const getContents = (context, payload) => {
  // Update the url
  updateUrlPath(payload);
  context.commit(types.SET_IS_LOADING, true);

  api.getContents(payload, 0)
    .then((contents) => {
      context.commit(types.LOAD_CONTENTS_SUCCESS, contents);
      context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      context.commit(types.SELECT_DIRECTORY, payload);
      context.commit(types.SET_IS_LOADING, false);
    })
    .catch((error) => {
      // @todo error handling
      context.commit(types.SET_IS_LOADING, false);
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
};

/**
 * Get the full contents of a directory
 * @param context
 * @param payload
 */
export const getFullContents = (context, payload) => {
  context.commit(types.SET_IS_LOADING, true);
  api.getContents(payload.path, 1)
    .then((contents) => {
      context.commit(types.LOAD_FULL_CONTENTS_SUCCESS, contents.files[0]);
      context.commit(types.SET_IS_LOADING, false);
    })
    .catch((error) => {
      // @todo error handling
      context.commit(types.SET_IS_LOADING, false);
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
};

/**
 * Download a file
 * @param context
 * @param payload
 */
export const download = (context, payload) => {
  api.getContents(payload.path, 0, 1)
    .then((contents) => {
      const file = contents.files[0];

      // Convert the base 64 encoded string to a blob
      const byteCharacters = atob(file.content);
      const byteArrays = [];

      for (let offset = 0; offset < byteCharacters.length; offset += 512) {
        const slice = byteCharacters.slice(offset, offset + 512);

        const byteNumbers = new Array(slice.length);
        // eslint-disable-next-line no-plusplus
        for (let i = 0; i < slice.length; i++) {
          byteNumbers[i] = slice.charCodeAt(i);
        }

        const byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
      }

      // Download file
      const blobURL = URL.createObjectURL(new Blob(byteArrays, { type: file.mime_type }));
      const a = document.createElement('a');
      a.href = blobURL;
      a.download = file.name;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    })
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
};

/**
 * Toggle the selection state of an item
 * @param context
 * @param payload
 */
export const toggleBrowserItemSelect = (context, payload) => {
  const item = payload;
  const isSelected = context.state.selectedItems.some((selected) => selected.path === item.path);
  if (!isSelected) {
    context.commit(types.SELECT_BROWSER_ITEM, item);
  } else {
    context.commit(types.UNSELECT_BROWSER_ITEM, item);
  }
};

/**
 * Create a new folder
 * @param context
 * @param payload object with the new folder name and its parent directory
 */
export const createDirectory = (context, payload) => {
  if (!api.canCreate) {
    return;
  }
  context.commit(types.SET_IS_LOADING, true);
  api.createDirectory(payload.name, payload.parent)
    .then((folder) => {
      context.commit(types.CREATE_DIRECTORY_SUCCESS, folder);
      context.commit(types.HIDE_CREATE_FOLDER_MODAL);
      context.commit(types.SET_IS_LOADING, false);
    })
    .catch((error) => {
      // @todo error handling
      context.commit(types.SET_IS_LOADING, false);
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
};

/**
 * Create a new folder
 * @param context
 * @param payload object with the new folder name and its parent directory
 */
export const uploadFile = (context, payload) => {
  if (!api.canCreate) {
    return;
  }
  context.commit(types.SET_IS_LOADING, true);
  api.upload(payload.name, payload.parent, payload.content, payload.override || false)
    .then((file) => {
      context.commit(types.UPLOAD_SUCCESS, file);
      context.commit(types.SET_IS_LOADING, false);
    })
    .catch((error) => {
      context.commit(types.SET_IS_LOADING, false);

      // Handle file exists
      if (error.status === 409) {
        if (notifications.ask(translate.sprintf('COM_MEDIA_FILE_EXISTS_AND_OVERRIDE', payload.name), {})) {
          payload.override = true;
          uploadFile(context, payload);
        }
      }
    });
};

/**
 * Rename an item
 * @param context
 * @param payload object: the item and the new path
 */
export const renameItem = (context, payload) => {
  if (!api.canEdit) {
    return;
  }

  if (typeof payload.item.canEdit !== 'undefined' && payload.item.canEdit === false) {
    return;
  }
  context.commit(types.SET_IS_LOADING, true);
  api.rename(payload.item.path, payload.newPath)
    .then((item) => {
      context.commit(types.RENAME_SUCCESS, {
        item,
        oldPath: payload.item.path,
        newName: payload.newName,
      });
      context.commit(types.HIDE_RENAME_MODAL);
      context.commit(types.SET_IS_LOADING, false);
    })
    .catch((error) => {
      // @todo error handling
      context.commit(types.SET_IS_LOADING, false);
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
};

/**
 * Delete the selected items
 * @param context
 */
export const deleteSelectedItems = (context) => {
  if (!api.canDelete) {
    return;
  }
  context.commit(types.SET_IS_LOADING, true);
  // Get the selected items from the store
  const { selectedItems, search } = context.state;
  if (selectedItems.length > 0) {
    selectedItems.forEach((item) => {
      if (
        (typeof item.canDelete !== 'undefined' && item.canDelete === false)
        || (search && !item.name.toLowerCase().includes(search.toLowerCase()))) {
        return;
      }
      api.delete(item.path)
        .then(() => {
          context.commit(types.DELETE_SUCCESS, item);
          context.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
          context.commit(types.SET_IS_LOADING, false);
        })
        .catch((error) => {
          // @todo error handling
          context.commit(types.SET_IS_LOADING, false);
          // eslint-disable-next-line no-console
          console.log('error', error);
        });
    });
  } else {
    // @todo notify the user that he has to select at least one item
  }
};
