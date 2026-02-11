import { createHash } from 'node:crypto';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';

const skipExternal = true;
const variable = 'v';

function version(urlString, fromFile, withHash) {
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

  if (withHash) {
    return `${urlString}?${variable}=${withHash}`;
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
 * @param {withHash: String} - the filepath for the css file
 * @returns {import('lightningcss').Visitor} - A visitor that replaces the url
 */
const urlVersioning2 = (withHash) => ({
  /**
   * @param {import('lightningcss').Url} url - The url object to transform
   * @returns {import('lightningcss').Url} - The transformed url object
   */
  Url: (url) => ({ ...url, url: version(url.url, false, withHash) }),
});

export { urlVersioning, urlVersioning2 };
