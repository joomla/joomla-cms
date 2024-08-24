const { lstat, readFile, writeFile } = require('fs-extra');
const { sep, basename } = require('path');
const recursive = require('recursive-readdir');
const { transform } = require('esbuild');

const RootPath = process.cwd();

const folders = [
  'media/vendor/accessibility/js',
  'media/vendor/chosen/js',
  'media/vendor/codemirror',
  'media/vendor/debugbar',
  'media/vendor/diff/js',
  'media/vendor/es-module-shims/js',
  'media/vendor/qrcode/js',
  'media/vendor/short-and-sweet/js',
  'media/vendor/webcomponentsjs/js',
];

let allFiles = [];

const noMinified = [
  'accessibility.min.js',
  'short-and-sweet.min.js',
];

const alreadyMinified = [
  'media/vendor/webcomponentsjs/js/webcomponents-bundle.js',
  'media/vendor/debugbar/vendor/highlightjs/highlight.pack.js',
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
  const needsDotJS = noMinified.includes(basename(file));
  if (file.endsWith('.min.js') && !needsDotJS) {
    return;
  }

  // eslint-disable-next-line no-console
  console.log(`Processing Vendor file: ${file}`);

  let minified;
  const fileExists = await minifiedExists(file);
  if (!fileExists) {
    return;
  }

  const content = await readFile(file, { encoding: 'utf8' });

  const isMinified = alreadyMinified.includes(file.replace(`${RootPath}${sep}`, ''));
  if (isMinified || needsDotJS) {
    minified = content;
  } else {
    minified = (await transform(content, { minify: true })).code;
  }

  const newFile = needsDotJS ? file.replace('.min.js', '.js') : file.replace('.js', '.min.js');
  // Write the file
  await writeFile(
    newFile,
    minified,
    { encoding: 'utf8', mode: 0o644 },
  );
};

/**
 * Method that will minify a set of vendor javascript files
 *
 * @returns {Promise}
 */
module.exports.minifyVendor = async () => {
  const folderPromises = [];
  const filesPromises = [];

  folders.map((folder) => folderPromises.push(recursive(folder, ['!*.+(js)'])));

  const computedFiles = await Promise.all(folderPromises);
  allFiles = [...allFiles, ...[].concat(...computedFiles)];
  allFiles.map((file) => filesPromises.push(minifyJS(file)));

  return Promise.all(filesPromises);
};
