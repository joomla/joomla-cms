const { readFile, writeFile } = require('fs-extra');
const { lstat } = require('fs').promises;
const { sep } = require('path');
const { minify } = require('terser');

const RootPath = process.cwd();

const allFiles = [
  'media/vendor/accessibility/js/accessibility.min.js',
  'media/vendor/chosen/js/chosen.jquery.js',
  'media/vendor/codemirror/lib/addons.js',
  'media/vendor/codemirror/lib/addons.js',
  'media/vendor/codemirror/lib/codemirror-ce.js',
  'media/vendor/debugbar/debugbar.js',
  'media/vendor/debugbar/openhandler.js',
  'media/vendor/debugbar/widgets.js',
  'media/vendor/punycode/js/punycode.js',
  'media/vendor/qrcode/js/qrcode.js',
  'media/vendor/qrcode/js/qrcode_SJIS.js',
  'media/vendor/qrcode/js/qrcode_UTF8.js',
  'media/vendor/short-and-sweet/js/short-and-sweet.min.js',
  'media/vendor/webcomponentsjs/js/webcomponents-ce.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd-ce.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd-ce-pf.js',
];

const noMinified = [
  'media/vendor/accessibility/js/accessibility.min.js',
  'media/vendor/short-and-sweet/js/short-and-sweet.min.js',
];

const alreadyMinified = [
  'media/vendor/webcomponentsjs/js/webcomponents-ce.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd-ce.js',
  'media/vendor/webcomponentsjs/js/webcomponents-sd-ce-pf.js',
];

/**
 * Check if a file exists
 *
 * @param file
 * @returns {Promise<boolean>}
 */
const minifiedExists = async (file) => {
  try {
    return (await lstat(file)).isFile();
  } catch (e) {
    return false;
  }
};

/**
 *
 * @param {string} file
 *
 * @returns {Promise}
 */
const minifyJS = async (file) => {
  // eslint-disable-next-line no-console
  console.log(`Processing Vendor file: ${file}`);

  let minified;
  const fileExists = await minifiedExists(file);
  if (!fileExists) {
    return;
  }

  const content = await readFile(file, { encoding: 'utf8' });
  const needsDotJS = noMinified.includes(file.replace(`${RootPath}${sep}`, ''));
  const isMinified = alreadyMinified.includes(file.replace(`${RootPath}${sep}`, ''));
  if (isMinified || needsDotJS) {
    minified = content;
  } else {
    minified = (await minify(content, { sourceMap: false, format: { comments: false } })).code;
  }

  const newFile = needsDotJS ? file.replace('.min.js', '.js') : file.replace('.js', '.min.js');
  // Write the file
  await writeFile(
    newFile,
    minified,
    { encoding: 'utf8' },
  );
};

/**
 * Method that will minify a set of vendor javascript files
 *
 * @returns {Promise}
 */
module.exports.minifyVendor = async () => {
  // return;
  const filesPromises = [];

  allFiles.map((file) => filesPromises.push(minifyJS(file)));

  return Promise.all(filesPromises);
};
