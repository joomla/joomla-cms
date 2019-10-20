const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs');
const FsExtra = require('fs-extra');
const Path = require('path');
const Postcss = require('postcss');
const Sass = require('node-sass');

module.exports.compile = (file) => {
  const cssFile = file.replace('/scss/', '/css/').replace('\\scss\\', '\\css\\')
    .replace('.scss', '.css').replace('/build/media_source/', '/media/')
    .replace('\\build\\media_source\\', '\\media\\');

  Sass.render({
    file,
  },
  (error, result) => {
    if (error) {
      // eslint-disable-next-line no-console
      console.error(error.formatted);
      process.exit(1);
    } else {
      // Auto prefixing
      // eslint-disable-next-line no-console
      const cleaner = Postcss(
        [
          Autoprefixer(),
        ],
      );

      cleaner.process(result.css.toString(), { from: undefined })
        .then((res) => {
          // Ensure the folder exists or create it
          FsExtra.mkdirsSync(Path.dirname(cssFile), {});

          Fs.writeFileSync(
            cssFile,
            res.css.toString(),
            { encoding: 'utf8', mode: 0o2644 },
          );

          Postcss([CssNano]).process(res.css.toString(), { from: undefined }).then((cssMin) => {
            // Ensure the folder exists or create it
            FsExtra.mkdirsSync(Path.dirname(cssFile.replace('.css', '.min.css')), {});
            Fs.writeFileSync(
              cssFile.replace('.css', '.min.css'),
              cssMin.css.toString(),
              { encoding: 'utf8', mode: 0o2644 },
            );

            // eslint-disable-next-line no-console
            console.log(`SCSS File compiled: ${cssFile}`);
          });
        });
    }
  });
};
