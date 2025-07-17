import { join } from 'node:path';
import { readFile, writeFile } from 'node:fs/promises';

const RootPath = process.cwd();

/**
 * Main method that will patch files...
 *
 * @param options The options from setting.json
 *
 * @returns {Promise}
 */
export const patchPackages = async () => {
  const mediaVendorPath = join(RootPath, 'media/vendor');

  // Include the v5 shim for Font Awesome
  const faPath = join(mediaVendorPath, 'fontawesome-free/scss/fontawesome.scss');
  const newScss = (await readFile(faPath, { encoding: 'utf8' })).concat(`
@import 'shims';
`);
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
};
