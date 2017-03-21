const path = require('path');

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

        this._baseUrl = options.apiBaseUrl;
        this._csrfToken = options.csrfToken;
    }

    /**
     * Get the contents of a directory from the server
     * @param dir
     * @returns {Promise}
     */
    getContents(dir) {
        // Wrap the jquery call into a real promise
        return new Promise((resolve, reject) => {
            const url = this._baseUrl + '&task=api.files&path=' + dir;
            jQuery.getJSON(url)
                .done((json) => resolve(this._normalizeArray(json.data)))
                .fail((xhr, status, error) => {
                    reject(xhr)
                })
        }).catch(this._handleError);
    }

    /**
     * Create a directory
     * @param name
     * @param parent
     * @returns {Promise.<T>}
     */
    createDirectory(name, parent) {
        // Wrap the jquery call into a real promise
        return new Promise((resolve, reject) => {
            const url = this._baseUrl + '&task=api.files&path=' + parent;
            const data = {[this._csrfToken]: '1', name: name};
            jQuery.ajax({
                url: url,
                type: "POST",
                data: JSON.stringify(data),
                contentType: "application/json",
            })
                .done((json) => resolve(this._normalizeItem(json.data)))
                .fail((xhr, status, error) => {
                    reject(xhr)
                })
        }).catch(this._handleError);
    }

    /**
     * Normalize a single item
     * @param item
     * @returns {*}
     * @private
     */
    _normalizeItem(item) {
        if (item.type === 'dir') {
            item.directories = [];
            item.files = [];
        }

        item.directory = path.dirname(item.path);

        return item;
    }

    /**
     * Normalize array data
     * @param data
     * @returns {{directories, files}}
     * @private
     */
    _normalizeArray(data) {

        const directories = data.filter(item => (item.type === 'dir'))
            .map(directory => this._normalizeItem(directory));
        const files = data.filter(item => (item.type === 'file'))
            .map(file => this._normalizeItem(file));

        return {
            directories: directories,
            files: files,
        }
    }

    /**
     * Handle errors
     * @param error
     * @private
     *
     * @TODO DN improve error handling
     */
    _handleError(error) {
        alert(error.status + ' ' + error.statusText);
        switch (error.status) {
            case 404:
                break;
            case 401:
            case 403:
            case 500:
                window.location.href = window.location.pathname;
            default:
                window.location.href = window.location.pathname;
        }

        throw error;
    }
}

export let api = new Api();