const options = require('../../../package.json');
const settings = require('../settings.json');

/**
 * Method to get the root path
 *
 * @returns {string} The root path
 */

// Merge Joomla's specific settings to the main package.json object
if ('settings' in settings) {
  options.settings = settings.settings;
}


module.exports.options = options;
