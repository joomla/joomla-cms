const { createHash } = require('node:crypto');
const { readdir, readFile, writeFile } = require('fs/promises');
const { existsSync, readFileSync } = require('node:fs');
const { dirname, extname, resolve } = require('node:path');
const { transform, composeVisitors } = require('lightningcss');
const { Timer } = require('./utils/timer.es6.js');

const skipExternal = true;
const variable = 'v';

function version(urlString, fromFile) {
  // Skip external URLs
  if (skipExternal && (urlString.startsWith('http') || urlString.startsWith('//'))) {
    return `${urlString}`;
  }
  // Skip base64 URLs
  if (urlString.startsWith('data:')) {
    return `${urlString}`;
  }
  // Skip URLs with existing query
  if (urlString.includes('?')) {
    return `${urlString}`;
  }

  if (fromFile && existsSync(resolve(`${dirname(fromFile)}/${urlString}`))) {
    const hash = createHash('md5');
    hash.update(readFileSync(resolve(`${dirname(fromFile)}/${urlString}`)));

    return `${urlString}?${variable}=${hash.digest('hex').substring(0, 6)}`;
  }

  return `${urlString}?${variable}=${(new Date()).valueOf().toString().substring(0, 6)}`;
}

/**
 * @param {from: String} - the filepath for the css file
 * @returns {import('lightningcss').Visitor} - A visitor that replaces the url
 */
function urlVersioning(fromFile) {
  return {
    /**
     * @param {import('lightningcss').Url} url - The url object to transform
     * @returns {import('lightningcss').Url} - The transformed url object
     */
    Url(url) {
      return { ...url, ...{ url: version(url.url, fromFile) } };
    },
  };
}

/**
 * Adds a hash to the url() parts of the static css
 *
 * @param file
 * @returns {Promise<void>}
 */
const fixVersion = async (file) => {
  try {
    const cssString = await readFile(file);
    const { code } = transform({
      code: cssString,
      minify: false,
      visitor: composeVisitors([urlVersioning(file)]),
    });
    await writeFile(file, code, { encoding: 'utf8', mode: 0o644 });
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

  const cssFiles = (await readdir('media', { withFileTypes: true, recursive: true }))
    .filter((file) => (!file.isDirectory() && extname(file.name) === '.css'))
    .map((file) => `${file.path}/${file.name}`);

  Promise.all(cssFiles.map((file) => fixVersion(file)))
    .then(() => bench.stop());
};
