/**
 * Method to get the root path
 *
 * @returns {string} The root path
 */
const rootPath = () => __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');

module.exports._ = rootPath;
