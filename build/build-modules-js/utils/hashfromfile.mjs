import { createReadStream } from 'node:fs';
import { createHash } from 'node:crypto';

/**
 * Get a hash (MD5) for a given file
 * @param filePath
 * @returns {Promise<unknown>}
 */
export const createHashFromFile = (filePath) => new Promise((res) => {
  const hash = createHash('md5');
  createReadStream(filePath)
    .on('data', (data) => hash.update(data))
    .on('end', () => res(hash.digest('hex')));
});
