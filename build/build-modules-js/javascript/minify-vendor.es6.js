const Fs = require('fs');
const Path = require('path');
const UglifyJS = require('uglify-es');
const walkSync = require('walk-sync');

const RootPath = process.cwd();

/**
 * Method that will minify a set of vendor javascript files
 */
module.exports.compile = () => {
  Promise.resolve()
    .then(() => {
      const folders = [
        Path.join(RootPath, 'media/vendor/codemirror'),
        Path.join(RootPath, 'media/vendor/punycode/js'),
        Path.join(RootPath, 'media/vendor/webcomponentsjs'),
      ];

      // Loop to get some text for the package.json
      folders.forEach((folder) => {
        const files = walkSync(folder, {
          globs: ['**/*.js'],
          includeBasePath: true,
          ignore: [],
          directories: false,
        });

        if (files.length) {
          files.forEach(
            (file) => {
              if (file.match(/\.js/) && !file.match(/LICENSE\.md/)) {
                // eslint-disable-next-line no-console
                console.log(`Processing ES5 file: ${file}`);
                // Write the file
                Fs.writeFileSync(
                  file.replace('.js', '.min.js'),
                  UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code,
                  { encoding: 'utf8' },
                );
              }
            },
          );
        }
      });
    })

    // Handle errors
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    });
};
