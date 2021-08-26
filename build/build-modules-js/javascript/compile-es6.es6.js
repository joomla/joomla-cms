const { lstat, readdir, readFile } = require('fs').promises;
const { dirname, sep } = require('path');
const { ensureDir } = require('fs-extra');
const { BabelTransform } = require('./babel-transform.es6.js');

const headerText = `PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.`;

/**
 * Check if a file exists
 *
 * @param file
 * @returns {Promise<boolean>}
 */
const folderExists = async (folder) => {
  try {
    return (await lstat(folder)).isDirectory();
  } catch (e) {
    return false;
  }
};

// Predefine some settings
const settings = [
  {
    presets: [
      ['@babel/preset-env', {
        targets: {
          browsers: ['ie 11'],
        },
        modules: false,
      }],
    ],
    plugins: [
      ['add-header-comment', { header: [headerText] }],
      ['@babel/plugin-transform-classes'],
    ],
    comments: true,
  },
  {
    presets: [
      ['@babel/preset-env', {
        targets: {
          browsers: ['> 5%', 'not ie 11'],
        },
        modules: false,
      }],
    ],
    plugins: [
      ['add-header-comment', { header: [headerText] }],
    ],
    comments: true,
  },
];

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMFile = async (file) => {
  const filePath = file.slice(0, -7);
  const newPath = filePath.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const outputFiles = [
    `${newPath}.js`,
    `${newPath}.es6.js`,
  ];

  // Ensure that the directories exist or create them
  ensureDir(dirname(file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)));

  // Get the contents of the ES-XXXX file
  let es6File = await readFile(file, { encoding: 'utf8' });
  const es6FileAsync = [];
  const es6Subdir = file.replace('es6.js', 'es6');
  const coreWeirdFolderExists = await folderExists(es6Subdir);

  if (coreWeirdFolderExists) {
    const allCorefilesPromises = [];
    const concatenateFileContents = async (es6SubFile) => {
      es6FileAsync.push(await readFile(`${es6Subdir}/${es6SubFile}`, { encoding: 'utf8' }));
    };
    const es6SubFiles = await readdir(es6Subdir);
    es6SubFiles.sort();
    es6SubFiles.map((es6SubFile) => allCorefilesPromises.push(concatenateFileContents(es6SubFile)));
    await Promise.all(allCorefilesPromises);
  }

  if (es6FileAsync.length) {
    es6File += es6FileAsync.join('');
  }

  const jobs = [];
  settings.forEach((setting, index) => {
    // eslint-disable-next-line no-console
    console.error(`Transpiling ES6 file: ${file}`);
    jobs.push(BabelTransform(es6File, setting, outputFiles[index]));
  });

  return Promise.all(jobs);
};
