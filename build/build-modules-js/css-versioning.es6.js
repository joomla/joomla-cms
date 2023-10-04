const { readFile, writeFile } = require('fs/promises');
const { existsSync, readFileSync } = require('fs');
const { resolve, dirname } = require('path');
const jetpack = require('fs-jetpack');
const Postcss = require('postcss');
const UrlVersion = require('postcss-url-version');
const { Timer } = require('./utils/timer.es6.js');

global.crypto = require('crypto');

const opts = {
  version: (imagePath, sourceCssPath) => {
    if (!sourceCssPath) {
      return (new Date()).valueOf().toString();
    }

    const directory = dirname(sourceCssPath);
    if (!(imagePath.startsWith('http') || imagePath.startsWith('//')) && existsSync(resolve(`${directory}/${imagePath}`))) {
      const fileBuffer = readFileSync(resolve(`${directory}/${imagePath}`));
      const hashSum = crypto.createHash('md5');
      hashSum.update(fileBuffer);

      return (hashSum.digest('hex')).substring(0, 6);
    }

    return (new Date()).valueOf().toString();
  },
};

/**
 * Adds a hash to the url() parts of the static css
 *
 * @param file
 * @returns {Promise<void>}
 */
const fixVersion = async (file) => {
  try {
    const cssString = await readFile(file, { encoding: 'utf8' });
    const data = await Postcss([UrlVersion(opts)]).process(cssString, { from: file });
    await writeFile(file, data.css, { encoding: 'utf8', mode: 0o644 });
  } catch (error) {
    throw new Error(error);
  }
};

/**
 * Loop the media folder and add version to all url() entries in all the css files
 *
 * @returns {Promise<void>}
 */
module.exports.cssVersioning = async () => {
  const bench = new Timer('Versioning');

  const cssFiles = jetpack.find('media', { matching: '/**/**/*.css' });
  await Promise.all(cssFiles.map((file) => fixVersion(file)));

  bench.stop();
