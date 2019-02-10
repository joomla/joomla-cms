const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs');
const Postcss = require('postcss');
const Sass = require('node-sass');
const Babel = require('./babel-transform.es6.js');

// The settings
const { options } = require('../utils/get-options.es6');

const createJsFiles = (inputFile, es6FileContents, folder) => {
  let outputFiles = [];

  // Define some settings
  const settings = [
    {
      presets: [
        ['@babel/preset-env', {
          targets: {
            browsers: ['last 1 Chrome version'],
          },
        }],
      ],
      comments: false,
    },
    {
      presets: [
        ['@babel/preset-env', {
          targets: {
            browsers: ['last 1 Chrome version'],
          },
        }],
        ['minify'],
      ],
      comments: false,
    },
    {
      presets: [
        ['@babel/preset-env', {
          targets: {
            browsers: ['ie 11'],
          },
        }],
      ],
      plugins: [
        '@babel/plugin-transform-classes',
      ],
      comments: false,

    },
    {
      presets: [

        ['@babel/preset-env', {
          targets: {
            browsers: ['ie 11'],
          },
        }],
        ['minify'],
      ],
      plugins: [
        ['@babel/plugin-transform-classes'],
      ],
      comments: false,

    },
  ];

  if (folder === 'media') {
    outputFiles = [
      inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '.js'),
      inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '.min.js'),
      inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '-es5.js'),
      inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '-es5.min.js'),
    ];
  } else if (folder === 'templates') {
    outputFiles = [
      inputFile.replace('/assets_source/js/', '/js/').replace('\\assets_source\\js\\', '\\js\\').replace('.w-c.es6.js', '.js'),
      inputFile.replace('/assets_source/js/', '/js/').replace('\\assets_source\\js\\', '\\js\\').replace('.w-c.es6.js', '.min.js'),
      inputFile.replace('/assets_source/js/', '/js/').replace('\\assets_source\\js\\', '\\js\\').replace('.w-c.es6.js', '-es5.js'),
      inputFile.replace('/assets_source/js/', '/js/').replace('\\assets_source\\js\\', '\\js\\').replace('.w-c.es6.js', '-es5.min.js'),
    ];
  }

  settings.forEach((setting, index) => {
    Babel.run(es6FileContents, setting, outputFiles[index]);
  });
};

/**
 * Compiles any web component/custom element files from the media_source folder
 *
 * @param file     The full path to the file + filename + extension
 */
module.exports.compile = (inputFile, folder) => {
  Promise.resolve()
    .then(() => {
      // Get the contents of the ES-XXXX file
      let es6File = Fs.readFileSync(inputFile, 'utf8');
      let scssFile = '';

      if (folder === 'media') {
        scssFile = inputFile.replace('/js/', '/scss/').replace('\\js\\', '\\scss\\').replace('.w-c.es6.js', '.scss');
      } else if (folder === 'templates') {
        scssFile = inputFile.replace('/assets_source/js/', '/assets_source/scss/').replace('\\assets_source\\js\\', '\\assets_source\\scss\\').replace('.w-c.es6.js', '.scss');
      }

      // Check if there is a css file
      if (Fs.existsSync(scssFile)) {
        Sass.render({
          file: scssFile,
        }, (error, result) => {
          if (error) {
            // eslint-disable-next-line no-console
            console.error(`${error.column}
                      ${error.message}
                      ${error.line}`);
          } else {
            const cleaner = Postcss(
              [
                Autoprefixer({
                  env: {
                    targets: {
                      browsers: [options.settings.browsers],
                    },
                  },
                }),
              ],
            );

            if (typeof result === 'object' && result.css) {
              cleaner.process(result.css.toString(), { from: undefined })
                .then((res) => {
                  if (/{{CSS_CONTENTS_PLACEHOLDER}}/.test(es6File)) {
                    if (typeof res === 'object' && res.css) {
                      // eslint-disable-next-line max-len
                      Postcss([CssNano]).process(res.css.toString(), { from: undefined }).then((cssMin) => {
                        es6File = es6File.replace('{{CSS_CONTENTS_PLACEHOLDER}}', cssMin.css.toString());

                        createJsFiles(inputFile, es6File, folder);
                      });
                    }
                  } else {
                    if (typeof res === 'object' && res.css) {
                      let scssOutFile = '';

                      if (folder === 'media') {
                        scssOutFile = inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('/js/', '/css/').replace('\\js\\', '\\css\\');
                      } else if (folder === 'templates') {
                        scssOutFile = inputFile.replace('/assets_source/js/', '/css/').replace('\\assets_source\\js\\', '\\css\\');
                      }

                      Fs.writeFileSync(
                        scssOutFile.replace('.w-c.es6.js', '.css'),
                        res.css.toString(),
                        { encoding: 'UTF-8' },
                      );
                      // eslint-disable-next-line max-len
                      Postcss([CssNano]).process(res.css.toString(), { from: undefined }).then((cssMin) => {
                        Fs.writeFileSync(
                          scssOutFile.replace('.w-c.es6.js', '.min.css'),
                          cssMin.css.toString(),
                          { encoding: 'UTF-8' },
                        );
                      });
                    }

                    createJsFiles(inputFile, es6File, folder);
                  }
                })

                // Handle errors
                .catch((err) => {
                  // eslint-disable-next-line no-console
                  console.error(`${err}`);
                  process.exit(-1);
                });
            }
          }
        });
      } else {
        createJsFiles(inputFile, es6File, folder);
      }
    })

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${err}`);
      process.exit(-1);
    });
};
