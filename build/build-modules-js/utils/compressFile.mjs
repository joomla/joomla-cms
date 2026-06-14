import { readFile, writeFile } from 'node:fs/promises';
import { promisify } from 'node:util';
import { constants, gzip, brotliCompress } from 'node:zlib';

const gzipOpts = {
  level: constants.Z_BEST_COMPRESSION,
};

const brotliOpts = {
  params: {
    [constants.BROTLI_PARAM_MODE]: constants.BROTLI_MODE_TEXT,
    [constants.BROTLI_PARAM_QUALITY]: constants.BROTLI_MAX_QUALITY,
  },
};

const gzipPromise = promisify(gzip);
const gzipEncode = (data) => gzipPromise(data, gzipOpts);

const brotliPromise = promisify(brotliCompress);
const brotliEncode = (data) => brotliPromise(data, brotliOpts);

/**
 * Compress and store the result aside original file.
 * @param { String } file
 * @param { boolean } enableBrotli
 * @return {Promise<void>}
 */
export const compressFileAndSave = async (file, enableBrotli = false) => {
  return readFile(file).then((content) => {
    const gzipRun = gzipEncode(content).then((data) => {
      return writeFile(`${file}.gz`, data);
    });
    const brotliRun = !enableBrotli ? Promise.resolve() : brotliEncode(content).then(() => {
      return writeFile(`${file}.br`, data);
    });

    return Promise.all([gzipRun, brotliRun]);
  });
};
