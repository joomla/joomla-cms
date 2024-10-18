import { basename } from 'node:path';
import { readFile, writeFile } from 'node:fs/promises';

import { transform } from 'esbuild';

/**
 * Minify a js file using esbuild
 *
 * @param file
 * @returns {Promise<void>}
 */
export const minifyFile = async (file) => {
  const fileContent = await readFile(file, { encoding: 'utf8' });
  const content = await transform(fileContent, { minify: true });
  await writeFile(file.replace('.js', '.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
  // eslint-disable-next-line no-console
  console.log(`✅ Legacy js file: ${basename(file)}: minified`);
};

/**
 * Minify a chunk of js using esbuild
 *
 * @param code
 * @returns {Promise<void>}
 */
export const minifyCode = async (code) => transform(code, { minify: true });
