import { notifications } from './Notifications.es6';
import { dirname } from './path';

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

    // eslint-disable-next-line no-underscore-dangle
    this._baseUrl = options.apiBaseUrl;
    // eslint-disable-next-line no-underscore-dangle
    this._csrfToken = Joomla.getOptions('csrf.token');

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
     * @param {string}  dir  The directory path
     * @param {number}  full whether or not the persistent url should be returned
     * @param {number}  content whether or not the content should be returned
     * @returns {Promise}
     */
  getContents(dir, full, content) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // Do a check on full
      if (['0', '1'].indexOf(full) !== -1) {
        throw Error('Invalid parameter: full');
      }
      // Do a check on download
      if (['0', '1'].indexOf(content) !== -1) {
        throw Error('Invalid parameter: content');
      }

      // eslint-disable-next-line no-underscore-dangle
      let url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(dir)}`;

      if (full) {
        url += `&url=${full}`;
      }

      if (content) {
        url += `&content=${content}`;
      }

      Joomla.request({
        url,
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          // eslint-disable-next-line no-underscore-dangle
          resolve(this._normalizeArray(JSON.parse(response).data));
        },
        onError: (xhr) => {
          reject(xhr);
        },
      });
      // eslint-disable-next-line no-underscore-dangle
    }).catch(this._handleError);
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
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`;
      // eslint-disable-next-line no-underscore-dangle
      const data = { [this._csrfToken]: '1', name };

      Joomla.request({
        url,
        method: 'POST',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_CREATE_NEW_FOLDER_SUCCESS');
          // eslint-disable-next-line no-underscore-dangle
          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          notifications.error('COM_MEDIA_CREATE_NEW_FOLDER_ERROR');
          reject(xhr);
        },
      });
      // eslint-disable-next-line no-underscore-dangle
    }).catch(this._handleError);
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
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`;
      const data = {
        // eslint-disable-next-line no-underscore-dangle
        [this._csrfToken]: '1',
        name,
        content,
      };

      // Append override
      if (override === true) {
        data.override = true;
      }

      Joomla.request({
        url,
        method: 'POST',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_UPLOAD_SUCCESS');
          // eslint-disable-next-line no-underscore-dangle
          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          reject(xhr);
        },
      });
      // eslint-disable-next-line no-underscore-dangle
    }).catch(this._handleError);
  }

  /**
     * Rename an item
     * @param path
     * @param newPath
     * @return {Promise.<T>}
     */
  // eslint-disable-next-line no-shadow
  rename(path, newPath) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(path)}`;
      const data = {
        // eslint-disable-next-line no-underscore-dangle
        [this._csrfToken]: '1',
        newPath,
      };

      Joomla.request({
        url,
        method: 'PUT',
        data: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (response) => {
          notifications.success('COM_MEDIA_RENAME_SUCCESS');
          // eslint-disable-next-line no-underscore-dangle
          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: (xhr) => {
          notifications.error('COM_MEDIA_RENAME_ERROR');
          reject(xhr);
        },
      });
      // eslint-disable-next-line no-underscore-dangle
    }).catch(this._handleError);
  }

  /**
     * Delete a file
     * @param path
     * @return {Promise.<T>}
     */
  // eslint-disable-next-line no-shadow
  delete(path) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(path)}`;
      // eslint-disable-next-line no-underscore-dangle
      const data = { [this._csrfToken]: '1' };

      Joomla.request({
        url,
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
      // eslint-disable-next-line no-underscore-dangle
    }).catch(this._handleError);
  }

  /**
     * Normalize a single item
     * @param item
     * @returns {*}
     * @private
     */
  // eslint-disable-next-line no-underscore-dangle,class-methods-use-this
  _normalizeItem(item) {
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
  // eslint-disable-next-line no-underscore-dangle
  _normalizeArray(data) {
    const directories = data.filter((item) => (item.type === 'dir'))
      // eslint-disable-next-line no-underscore-dangle
      .map((directory) => this._normalizeItem(directory));
    const files = data.filter((item) => (item.type === 'file'))
      // eslint-disable-next-line no-underscore-dangle
      .map((file) => this._normalizeItem(file));

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
  // eslint-disable-next-line no-underscore-dangle,class-methods-use-this
  _handleError(error) {
    const response = JSON.parse(error.response);
    if (response.message) {
      notifications.error(response.message);
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
}

// eslint-disable-next-line import/prefer-default-export
export const api = new Api();
