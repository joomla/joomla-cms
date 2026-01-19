import { createHash } from 'node:crypto';
import { readdir, readFile, writeFile } from 'node:fs/promises';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, extname, resolve } from 'node:path';
import { transform, composeVisitors } from 'lightningcss';
import { Timer } from '../utils/timer.mjs';

const RootPath = process.cwd();
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
  // Skip anchor for predefined templates
  if (urlString.includes('#')) {
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
const urlVersioning = (fromFile) => ({
  /**
   * @param {import('lightningcss').Url} url - The url object to transform
   * @returns {import('lightningcss').Url} - The transformed url object
   */
  Url: (url) => ({ ...url, url: version(url.url, fromFile) }),
});

/**
 * Adds a hash to the url() parts of the static css
 *
 * @param file
 * @returns {Promise<void>}
 */
const fixVersion = async (file) => {
  try {
    let content = await readFile(file, { encoding: 'utf8' });
    // To preserve the licence the comment needs to start at the beginning of the file
    const replaceUTF8String = file.endsWith('.min.css') ? '@charset "UTF-8";' : '@charset "UTF-8";\n';
    content = content.startsWith(replaceUTF8String) ? content.replace(replaceUTF8String, '') : content;

    // Preserve a leading license comment (/** ... */)
    const firstLine = content.split(/\r?\n/)[0] || '';
    if (firstLine.includes('/*') && !firstLine.includes('/*!')) {
      const endCommentIdx = content.indexOf('*/');
      if (endCommentIdx !== -1
          && (content.substring(0, endCommentIdx).includes('license')
          || content.substring(0, endCommentIdx).includes('copyright'))
      ) {
        content = firstLine.includes('/**') ? content.replace('/**', '/*!') : content.replace('/*', '/*!');
      }
    }

    const { code } = transform({
      code: Buffer.from(content),
      minify: file.endsWith('.min.css'),
      visitor: composeVisitors([urlVersioning(file)]),
    });
    await writeFile(file, `@charset "UTF-8";${file.endsWith('.min.css') ? '' : '\n'}${code}`, {
      encoding: 'utf8',
      mode: 0o644,
    });
  } catch (error) {
    throw new Error(error);
  }
};

/**
 * Loop the media folder and add version to all url() entries in all the css files
 *
 * @returns {Promise<void>}
 */
const cssVersioningVendor = async () => {
  const bench = new Timer('Versioning');

  const cssFiles = (await readdir(`${RootPath}/media/vendor`, { withFileTypes: true, recursive: true }))
    .filter((file) => (!file.isDirectory() && extname(file.name) === '.css'))
    .map((file) => `${file.parentPath}/${file.name}`);

  Promise.all(cssFiles.map((file) => fixVersion(file)))
    .then(() => bench.stop());
};

export { urlVersioning, cssVersioningVendor };
