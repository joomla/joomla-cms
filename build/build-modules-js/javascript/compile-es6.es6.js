const Fs = require('fs');
const Path = require('path');
const FsExtra = require('fs-extra');
const Babel = require('./babel-transform.es6.js');

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
 * @param file the full path to the file + filename + extension
 */
module.exports.compileFile = (file) => {
  Promise.resolve()
    .then(() => {
      const filePath = file.slice(0, -7);

      const outputFiles = [
        `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.js`,
        `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.min.js`,
        `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.es6.js`,
        `${filePath.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\')}.es6.min.js`,
      ];

      // Ensure that the directories exist or create them
      FsExtra.mkdirsSync(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'), {});

      // Get the contents of the ES-XXXX file
      let es6File = Fs.readFileSync(file, 'utf8');
      const es6Subdir = file.replace('es6.js', 'es6');

      if (Fs.existsSync(es6Subdir)) {
        const stats = Fs.lstatSync(es6Subdir);

        if (stats.isDirectory()) {
          const es6SubFiles = Fs.readdirSync(es6Subdir);
          es6SubFiles.sort();
          es6SubFiles.forEach((es6SubFile) => {
            es6File += Fs.readFileSync(`${es6Subdir}/${es6SubFile}`, 'utf8');
          });
        }
      }

      settings.forEach((setting, index) => {
      // eslint-disable-next-line no-console
        console.error(`Transpiling ES6 file: ${file}`);
        Babel.run(es6File, setting, outputFiles[index]);
      });
    })

  // Handle errors
    .catch((error) => {
    // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    });
};
