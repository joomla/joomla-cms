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
export const patchPackages = async (options) => {
  const mediaVendorPath = join(RootPath, 'media/vendor');

  // Joomla's hack to expose the chosen base classes so we can extend it ourselves
  // (it was better than the many hacks we had before. But I'm still ashamed of myself).
  const dest = join(mediaVendorPath, 'chosen');
  const chosenPath = `${dest}/${options.settings.vendors['chosen-js'].js['chosen.jquery.js']}`;
  let ChosenJs = await readFile(chosenPath, { encoding: 'utf8' });
  ChosenJs = ChosenJs.replace(
    '}).call(this);',
    `  document.AbstractChosen = AbstractChosen;
  document.Chosen = Chosen;
}).call(this);`,
  );
  await writeFile(chosenPath, ChosenJs, { encoding: 'utf8', mode: 0o644 });

  // Include the v5 shim for Font Awesome
  const faPath = join(mediaVendorPath, 'fontawesome-free/scss/fontawesome.scss');
  const newScss = (await readFile(faPath, { encoding: 'utf8' })).concat(`
@import 'shims';
`);
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
};
