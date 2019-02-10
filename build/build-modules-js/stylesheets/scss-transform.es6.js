const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs');
const Path = require('path');
const Postcss = require('postcss');
const Sass = require('node-sass');
const MakeDir = require('../utils/make-dir.es6.js');

// The settings
const { options } = require('../utils/get-options.es6');

module.exports.compile = (file, folder) => {
  let cssFile = '';
  if (folder === 'media') {
    cssFile = file.replace('/scss/', '/css/').replace('.scss', '.css').replace('/build/media_source/', '/media/');
  } else if (folder === 'templates') {
    cssFile = file.replace('/assets_source/scss/', '/css/').replace('.scss', '.css').replace('\\assets_source\\scss\\', '\\css\\');
  }

  Sass.render({
    file,
  },
  (error, result) => {
    if (error) {
      // eslint-disable-next-line no-console
      console.error(`something exploded ${error.column}`, error.message, error.line);
      process.exit(1);
    } else {
      // Auto prefixing
      // eslint-disable-next-line no-console
      const cleaner = Postcss(
        [
          Autoprefixer({
            browsers: options.settings.browsers,
          }),
        ],
      );

      cleaner.process(result.css.toString(), { from: undefined })
        .then((res) => {
          // Ensure the folder exists or create it
          MakeDir.run(Path.dirname(cssFile));

          Fs.writeFileSync(
            cssFile,
            res.css.toString(),
            { encoding: 'UTF-8' },
          );

          Postcss([CssNano]).process(res.css.toString(), { from: undefined }).then((cssMin) => {
            // Ensure the folder exists or create it
            MakeDir.run(Path.dirname(cssFile.replace('.css', '.min.css')));
            Fs.writeFileSync(
              cssFile.replace('.css', '.min.css'),
              cssMin.css.toString(),
              { encoding: 'UTF-8' },
            );

            // eslint-disable-next-line no-console
            console.log(`SCSS File compiled to: ${cssFile}`);
          });
        });
    }
  });
};
