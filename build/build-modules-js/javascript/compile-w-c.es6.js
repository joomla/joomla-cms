const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs');
const Postcss = require('postcss');
const Sass = require('node-sass');
const Babel = require('./babel-transform.es6.js');

const createJsFiles = (inputFile, es6FileContents) => {
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
      ],
      comments: false,

    },
  ];

  const outputFiles = [
    inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '.js'),
    inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '.min.js'),
    inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '-es5.js'),
    inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.w-c.es6.js', '-es5.min.js'),
  ];

  settings.forEach((setting, index) => {
    Babel.run(es6FileContents, setting, outputFiles[index]);
  });
};

/**
 * Compiles any web component/custom element files from the media_source folder
 *
 * @param file     The full path to the file + filename + extension
 */
module.exports.compile = (inputFile) => {
  Promise.resolve()
    .then(() => {
      // Get the contents of the ES-XXXX file
      let es6File = Fs.readFileSync(inputFile, 'utf8');
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
                Autoprefixer(),
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
                        createJsFiles(inputFile, es6File);
                      });
                    }
                  } else {
                    if (typeof res === 'object' && res.css) {
                      Fs.writeFileSync(
                        inputFile.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('/js/', '/css/').replace('\\js\\', '\\css\\')
                          .replace('.w-c.es6.js', '.css'),
                        res.css.toString(),
                        { encoding: 'utf8' },
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

                    createJsFiles(inputFile, es6File);
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

        createJsFiles(inputFile, es6File);
      }
    })

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${err}`);
      process.exit(-1);
    });
};
