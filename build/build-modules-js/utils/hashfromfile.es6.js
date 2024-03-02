const { createHash } = require('crypto');
const { createReadStream } = require('fs');

/**
 * Get a hash (MD5) for a given file
 * @param filePath
 * @returns {Promise<unknown>}
 */
module.exports.createHashFromFile = (filePath) => new Promise((res) => {
  const hash = createHash('md5');
  createReadStream(filePath).on('data', (data) => hash.update(data)).on('end', () => res(hash.digest('hex')));
});
