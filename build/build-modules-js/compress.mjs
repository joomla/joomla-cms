import { getFiles } from '@dgrammatiko/compress/src/getFiles.js';
import { compressFile } from '@dgrammatiko/compress/src/compressFile.js';
import { Timer } from './utils/timer.mjs';

/**
 * Method that will pre compress (gzip) all .css/.js files
 * in the templates and in the media folder
 */
const compressFiles = async (enableBrotli = false) => {
  const bench = new Timer('Gzip');
  const paths = [
    `${process.cwd()}/media`,
    `${process.cwd()}/installation/template`,
    `${process.cwd()}/templates`,
    `${process.cwd()}/administrator/templates`,
  ];

  const tasks = [];
  const compressTasks = [];
  paths.map((path) => tasks.push(getFiles(`${path}/`)));

  const files = await Promise.all(tasks);
  [].concat(...files).map((file) => compressTasks.push(compressFile(file, enableBrotli)));

  await Promise.all(compressTasks);
  // eslint-disable-next-line no-console
  console.log('✅ Done 👍');
  bench.stop();
};

export { compressFiles };
