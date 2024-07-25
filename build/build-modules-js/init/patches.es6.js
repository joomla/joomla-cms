const { readFile, writeFile } = require('fs-extra');
const { join } = require('path');

const RootPath = process.cwd();

/**
 * Main method that will patch files...
 *
 * @param options The options from setting.json
 *
 * @returns {Promise}
 */
module.exports.patchPackages = async (options) => {
  const mediaVendorPath = join(RootPath, 'media/vendor');

  // Joomla's hack to expose the chosen base classes so we can extend it ourselves
  // (it was better than the many hacks we had before. But I'm still ashamed of myself).
  let dest = join(mediaVendorPath, 'chosen');
  const chosenPath = `${dest}/${options.settings.vendors['chosen-js'].js['chosen.jquery.js']}`;
  let ChosenJs = await readFile(chosenPath, { encoding: 'utf8' });
  ChosenJs = ChosenJs.replace('}).call(this);', `  document.AbstractChosen = AbstractChosen;
  document.Chosen = Chosen;
}).call(this);`);
  await writeFile(chosenPath, ChosenJs, { encoding: 'utf8', mode: 0o644 });

  // Append initialising code to the end of the Short-and-Sweet javascript
  dest = join(mediaVendorPath, 'short-and-sweet');
  const shortandsweetPath = `${dest}/${options.settings.vendors['short-and-sweet'].js['dist/short-and-sweet.min.js']}`;
  let ShortandsweetJs = await readFile(shortandsweetPath, { encoding: 'utf8' });
  ShortandsweetJs = ShortandsweetJs.concat(`
shortAndSweet('textarea.charcount,input.charcount', {counterClassName: 'small text-muted'});
/** Repeatable */
document.addEventListener("joomla:updated", (event) => [].slice.call(event.target.querySelectorAll('textarea.charcount,input.charcount')).map((el) => shortAndSweet(el, {counterClassName: 'small text-muted'})));
`);
  await writeFile(shortandsweetPath, ShortandsweetJs, { encoding: 'utf8', mode: 0o644 });

  // Include the v5 shim for Font Awesome
  const faPath = join(mediaVendorPath, 'fontawesome-free/scss/fontawesome.scss');
  const newScss = (await readFile(faPath, { encoding: 'utf8' })).concat(`
@import 'shims';
`);
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
};
