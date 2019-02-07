const Fs = require('fs');
const Path = require('path');
const Babel = require('./babel-transform.es6.js');
const MakeDir = require('../utils/make-dir.es6.js');

const headerText = `PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.`;

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
          browsers: ['ie 11'],
        },
        modules: false,
      }],
      'minify',
    ],
    plugins: [
      ['@babel/plugin-transform-classes'],
    ],
    comments: false,
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
  {
    presets: [
      ['@babel/preset-env', {
        targets: {
          browsers: ['> 5%', 'not ie 11'],
        },
        modules: false,
      }],
      'minify',
    ],
    plugins: [],
    comments: false,
  },
];

/**
 * Compiles es6 files to es5.
 *
 * @param file    the full path to the file + filename + extension
 * @param folder  The container folder (media or templates)
 */
module.exports.compile = (file, folder) => {
  Promise.resolve()
    .then(() => {
      const filePath = file.slice(0, -7);

      let jsFile = '';

      if (folder === 'media') {
        jsFile = [
          `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.js`,
          `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.min.js`,
          `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.es6.js`,
          `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.es6.min.js`,
        ];
        // Ensure that the directories exist or create them
        MakeDir.run(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
      } else if (folder === 'templates') {
        jsFile = [
          `${filePath.replace('/src/js/', '/js/').replace('\\src\\js\\', '\\js\\')}.js`,
          `${filePath.replace('/src/js/', '/js/').replace('\\src\\js\\', '\\js\\')}.min.js`,
          `${filePath.replace('/src/js/', '/js/').replace('\\src\\js\\', '\\js\\')}.es6.js`,
          `${filePath.replace('/src/js/', '/js/').replace('\\src\\js\\', '\\js\\')}.es6.min.js`,
        ];

        // Ensure that the directories exist or create them
        MakeDir.run(Path.dirname(file).replace('/src/js/', '/js/').replace('\\src\\js\\', '\\js\\'));
      }


      // Get the contents of the ES-XXXX file
      const es6File = Fs.readFileSync(file, 'utf8');

      settings.forEach((setting, index) => {
        Babel.run(es6File, setting, jsFile[index]);
      });
    })

    // Handle errors
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    });
};
