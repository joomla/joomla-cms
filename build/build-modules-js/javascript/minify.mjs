import { basename } from 'node:path';
import { transform } from 'esbuild';

import pkg from 'fs-extra';

const { readFile, writeFile } = pkg;

/**
 * Minify a js file using Terser
 *
 * @param file
 * @returns {Promise<void>}
 */
const minifyFile = async (file) => {
  const fileContent = await readFile(file, { encoding: 'utf8' });
  const content = await transform(fileContent, { minify: true });
  await writeFile(file.replace('.js', '.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
  // eslint-disable-next-line no-console
  console.log(`✅ Legacy js file: ${basename(file)}: minified`);
};

/**
 * Minify a chunk of js using Terser
 *
 * @param code
 * @returns {Promise<void>}
 */
const minifyCode = async (code) => transform(code, { minify: true });

export { minifyFile, minifyCode };
