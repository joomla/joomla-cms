const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs');
const Path = require('path');
const Postcss = require('postcss');
const Sass = require('node-sass');
const Babel = require('./babel-transform.es6.js');
const RootPath = require('../utils/rootpath.es6.js')._();

const createJsFiles = (inputFile, es6FileContents, embededScript) => {
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
      plugins: [
        // ['iife-wrap'],
      ],
      comments: true,
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
      plugins: [
        // ['iife-wrap'],
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
        ['@babel/plugin-transform-classes'],
        // ['iife-wrap'],
      ],
      comments: true,

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
        // ['iife-wrap'],
      ],
      comments: false,

    },
  ];

  const path = inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\');
  const outputFiles = [
    path.replace('.w-c.es6.js', '.js'),
    path.replace('.w-c.es6.js', '.min.js'),
    path.replace('.w-c.es6.js', '-es5.js'),
    path.replace('.w-c.es6.js', '-es5.min.js'),
  ];

  settings.forEach((setting, index) => {
    Babel.run(es6FileContents, setting, outputFiles[index], embededScript);
  });
};

/**
 * Compiles any web component/custom element files from the media_source folder
 *
 * @param file     The full path to the file + filename + extension
 * @param options  The options from the settings.json
 */
module.exports.compile = (inputFile, options) => {
  Promise.resolve()
    .then(() => {
      let embededScript = '';
      // Get the contents of the ES-XXXX file
      let es6File = Fs.readFileSync(inputFile, 'utf8');

      // Embed another script if needed
      const reg = /^\/\*\*{{embed='?((?:.(?!["']?\s+(?:\S+)=|[>"']))+.)'}}\*\*\//;
      const parts = es6File.match(reg);
      if (parts && parts.length) {
        if (Fs.existsSync(Path.resolve(RootPath, parts[1]))) {
          const replacement = Fs.readFileSync(Path.resolve(RootPath, parts[1]), 'utf8');
          if (replacement) {
            embededScript = replacement;
          }
        }
      }

      // Check if there is a css file
      if (Fs.existsSync(inputFile.replace('/js/', '/scss/').replace('\\js\\', '\\scss\\').replace('.w-c.es6.js', '.scss'))) {
        Sass.render({
          file: inputFile.replace('/js/', '/scss/').replace('\\js\\', '\\scss\\').replace('.w-c.es6.js', '.scss'),
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
                        // eslint-disable-next-line no-console
                        console.error(`Transpiling Web Component file: ${inputFile}`);
                        createJsFiles(inputFile, es6File, embededScript);
                      });
                    }
                  } else {
                    if (typeof res === 'object' && res.css) {
                      Fs.writeFileSync(
                        inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('/js/', '/css/').replace('\\js\\', '\\css\\')
                          .replace('.w-c.es6.js', '.css'),
                        res.css.toString(),
                        { encoding: 'UTF-8' },
                      );
                      // eslint-disable-next-line max-len
                      Postcss([CssNano]).process(res.css.toString(), { from: undefined }).then((cssMin) => {
                        Fs.writeFileSync(
                          inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('/js/', '/css/').replace('\\js\\', '\\css\\')
                            .replace('.w-c.es6.js', '.min.css'),
                          cssMin.css.toString(),
                          { encoding: 'UTF-8' },
                        );
                      });
                    }

                    // eslint-disable-next-line no-console
                    console.error(`Transpiling Web Component file: ${inputFile}`);

                    createJsFiles(inputFile, es6File, embededScript);
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
        // eslint-disable-next-line no-console
        console.error(`Transpiling Web Component file: ${inputFile}`);

        createJsFiles(inputFile, es6File, embededScript);
      }
    })

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${err}`);
      process.exit(-1);
    });
};
