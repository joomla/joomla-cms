import notifications from './Notifications.es6';
import { dirname } from './path';

/**
 * Normalize a single item
 * @param item
 * @returns {*}
 * @private
 */
function normalizeItem(item) {
  if (item.type === 'dir') {
    item.directories = [];
    item.files = [];
  }

  item.directory = dirname(item.path);

  if (item.directory.indexOf(':', item.directory.length - 1) !== -1) {
    item.directory += '/';
  }

  return item;
}

/**
 * Normalize array data
 * @param data
 * @returns {{directories, files}}
 * @private
 */
function normalizeArray(data) {
  const directories = data.filter((item) => (item.type === 'dir'))
    .map((directory) => normalizeItem(directory));
  const files = data.filter((item) => (item.type === 'file'))
    .map((file) => normalizeItem(file));

  return {
    directories,
    files,
  };
}

/**
 * Handle errors
 * @param error
 * @private
 *
 * @TODO DN improve error handling
 */
function handleError(error) {
  const response = JSON.parse(error.response);
  if (response.message) {
    notifications.error(response.message);
    // Check for App messages queue
    if (response.messages) {
      Object.keys(response.messages).forEach((type) => {
        response.messages[type].forEach((message) => {
          if (type === 'error') {
            notifications.error(message);
          } else {
            notifications.notify(message);
          }
        });
      });
    }
  } else {
    switch (error.status) {
      case 409:
        // Handled in consumer
        break;
      case 404:
        notifications.error('COM_MEDIA_ERROR_NOT_FOUND');
        break;
      case 401:
        notifications.error('COM_MEDIA_ERROR_NOT_AUTHENTICATED');
        break;
      case 403:
        notifications.error('COM_MEDIA_ERROR_NOT_AUTHORIZED');
        break;
      case 500:
        notifications.error('COM_MEDIA_SERVER_ERROR');
        break;
      default:
        notifications.error('COM_MEDIA_ERROR');
    }
  }

  throw error;
}

/**
 * Api class for communication with the server
 */
class Api {
  /**
     * Store constructor
     */
  constructor() {
    const options = Joomla.getOptions('com_media', {});
    if (options.apiBaseUrl === undefined) {
      throw new TypeError('Media api baseUrl is not defined');
    }
    if (options.csrfToken === undefined) {
      throw new TypeError('Media api csrf token is not defined');
    }

    this.baseUrl = options.apiBaseUrl;
    this.csrfToken = Joomla.getOptions('csrf.token');

    this.imagesExtensions = options.imagesExtensions;
    this.audioExtensions = options.audioExtensions;
    this.videoExtensions = options.videoExtensions;
    this.documentExtensions = options.documentExtensions;
    this.mediaVersion = (new Date().getTime()).toString();
    this.canCreate = options.canCreate || false;
    this.canEdit = options.canEdit || false;
    this.canDelete = options.canDelete || false;
  }

  /**
     * Get the contents of a directory from the server
     * @param {string}   dir  The directory path
     * @param {boolean}  full whether or not the persistent url should be returned
     * @param {boolean}  content whether or not the content should be returned
     * @returns {Promise}
     */
  getContents(dir, full = false, content = false) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(`${this.baseUrl}&task=api.files&path=${encodeURIComponent(dir)}`);

      if (full) {
        url.searchParams.append('url', full);
      }

      if (content) {
        url.searchParams.append('content', content);
      }

      Joomla.request({
        url: url.toString(),
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          resolve(normalizeArray(JSON.parse(response).data));
        },
        onError: (xhr) => {
          reject(xhr);
        },
      });
    }).catch(handleError);
  }

  /**
     * Create a directory
     * @param name
     * @param parent
     * @returns {Promise.<T>}
     */
  createDirectory(name, parent) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(`${this.baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`);
      const data = { [this.csrfToken]: '1', name };

      Joomla.request({
        url: url.toString(),
        method: 'POST',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_CREATE_NEW_FOLDER_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          notifications.error('COM_MEDIA_CREATE_NEW_FOLDER_ERROR');
          reject(xhr);
        },
      });
    }).catch(handleError);
  }

  /**
     * Upload a file
     * @param name
     * @param parent
     * @param content base64 encoded string
     * @param override boolean whether or not we should override existing files
     * @return {Promise.<T>}
     */
  upload(name, parent, content, override) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(`${this.baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`);
      const data = {
        [this.csrfToken]: '1',
        name,
        content,
      };

      // Append override
      if (override === true) {
        data.override = true;
      }

      Joomla.request({
        url: url.toString(),
        method: 'POST',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_UPLOAD_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          reject(xhr);
        },
      });
    }).catch(handleError);
  }

  /**
     * Rename an item
     * @param path
     * @param newPath
     * @return {Promise.<T>}
     */
  rename(path, newPath) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(`${this.baseUrl}&task=api.files&path=${encodeURIComponent(path)}`);
      const data = {
        [this.csrfToken]: '1',
        newPath,
      };

      Joomla.request({
        url: url.toString(),
        method: 'PUT',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_RENAME_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          notifications.error('COM_MEDIA_RENAME_ERROR');
          reject(xhr);
        },
      });
    }).catch(handleError);
  }

  /**
     * Delete a file
     * @param path
     * @return {Promise.<T>}
     */
  delete(path) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(`${this.baseUrl}&task=api.files&path=${encodeURIComponent(path)}`);
      const data = { [this.csrfToken]: '1' };

      Joomla.request({
        url: url.toString(),
        method: 'DELETE',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: () => {
          notifications.success('COM_MEDIA_DELETE_SUCCESS');
          resolve();
        },
        onError: (xhr) => {
          notifications.error('COM_MEDIA_DELETE_ERROR');
          reject(xhr);
        },
      });
    }).catch(handleError);
  }
}

const api = new Api();
export default api;
