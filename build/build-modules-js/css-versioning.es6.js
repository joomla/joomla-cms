const { readFile, writeFile } = require('fs/promises');
const jetpack = require('fs-jetpack');
const Postcss = require('postcss');
const UrlVersion = require('postcss-url-version');
const { Timer } = require('./utils/timer.es6.js');

/**
 * Adds a hash to the url() parts of the static css
 *
 * @param file
 * @returns {Promise<void>}
 */
const fixVersion = async (file) => {
  try {
    const cssString = await readFile(file, { encoding: 'utf8' });
    const data = await Postcss([UrlVersion()]).process(cssString, { from: undefined });
    await writeFile(file, data.css, { encoding: 'utf8', mode: 0o644 });
  } catch (error) {
    throw new Error(error);
  }
};

/**
 * Loop the media folder and add version to all url() entries on all the css files
 *
 * @returns {Promise<void>}
 */
module.exports.cssVersioning = async () => {
  const bench = new Timer('Versioning');

  const cssFiles = jetpack.find('media', { matching: '/**/**/*.css' });
  await Promise.all(cssFiles.map((file) => fixVersion(file)));

  bench.stop();
};
