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

        this._baseUrl = options.apiBaseUrl;
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
                .success((json) => resolve(this._normalizeArray(json.data)))
                .fail((xhr, status, error) => {
                    reject(xhr)
                })
        }).catch(this._handleError);
    }

    /**
     * Normalize array data
     * @param data
     * @returns {{directories, files}}
     * @private
     */
    _normalizeArray(data) {

        // Directories
        const directories = data.filter(item => (item.type === 'dir'))
            .map(directory => {
                directory.directory = path.dirname(directory.path);
                directory.directories = [];
                directory.files = [];
                return directory;
            });

        // Files
        const files = data.filter(item => (item.type === 'file'))
            .map(file => {
                file.directory = path.dirname(file.path);
                return file;
            });

        return {
            directories: directories,
            files: files,
        }
    }

    /**
     * Handle errors
     * @param error
     * @private
     */
    _handleError(error) {
        alert(error.status + ' ' + error.statusText);
        switch (error.status) {
            case 404:
                break;
            case 401:
            case 403:
            case 500:
                window.location.href = '/administrator';
            default:
                window.location.href = '/administrator';
        }

        throw error;
    }
}

export let api = new Api();