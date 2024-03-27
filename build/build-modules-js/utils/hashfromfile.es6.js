const { createHash } = require('crypto');
const { createReadStream } = require('fs');

/**
 * Get a SHA1 hash for a given file
 * @param filePath
 * @returns {Promise<unknown>}
 */
module.exports.createHashFromFile = (filePath) => new Promise((res) => {
  const hash = createHash('sha1');
  createReadStream(filePath).on('data', (data) => hash.update(data)).on('end', () => res(hash.digest('hex')));
});
