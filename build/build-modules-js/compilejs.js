const Debounce = require('lodash.debounce');
const Fs = require('fs');
const Promise = require('bluebird');
const Recurs = require('recursive-readdir');
const RootPath = require('./rootpath.js')._();
const TranspileJs = require('./compile-es6.js');
const UglifyJS = require('uglify-es');

const watches = [
  `${RootPath}/media`,
  `${RootPath}/administrator/templates/atum/js`,
  `${RootPath}/templates/cassiopeia/js`,
];

const uglifyJs = (options, path) => {
  let folders = [];
  if (path) {
    const stats = Fs.lstatSync(`${RootPath}/${path}`);

    if (!stats.isDirectory()) {
      // @todo: allow to compile single file
      throw new Error(`Path should be a directory: ${path}`);
    }

    folders.push(`${RootPath}/${path}`);
  } else {
    folders = [
      `${RootPath}/build/media_src`,
      `${RootPath}/administrator/templates/atum/js`,
      `${RootPath}/templates/cassiopeia/js`,
    ];
  }

  // Loop to get some text for the packgage.json
  folders.forEach((folder) => {
    Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf', '*.json']).then(
      (files) => {
        files.forEach(
            (file) => {
                if (file.match(/\.es6\.js/)) {
                    // Transpile the file
                    TranspileJs.compileFile(file);
                } else {
                    Fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
                }
          },
          (error) => {
            // eslint-disable-next-line no-console
            console.error(`something exploded ${error}`);
          },
        );
      });
  });
};

const watchFiles = (options, folders, compileFirst = false) => {
  const folderz = folders || watches;

  if (compileFirst) {
    uglifyJs(options);
  }

  folderz.forEach(
  	(folder) => {
      Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf', '*.json']).then(
        (files) => {
          files.forEach(
            (file) => {
              if (file.match(/\.js/)) {
                Fs.watchFile(file, () => {
                  // eslint-disable-next-line no-console
                  console.warn(`File: ${file} changed.`);
                  Debounce(() => {
                    if (file.match(/\.es6\.js/)) {
                      // Transpile the file
                      TranspileJs.compileFile(file);
                    } else {
                        Fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
                    }
                  }, 150)();

                  // eslint-disable-next-line no-console
                  console.log(`${file} was updated.`);
                });
              }
            },
            (error) => {
                // eslint-disable-next-line no-console
                console.error(`${error}`);
                process.exit(1);
            },
          );
        }
      );
    }
  );

  // eslint-disable-next-line no-console
  console.log(`Now watching JS files...`);
};

const compileJS = (options, path) => {
  Promise.resolve()
    // Compile the scss files
    .then(() => uglifyJs(options, path))

    // Handle errors
    .catch((error) => {
      throw new Error(`${error}`);
    });
};

module.exports.compileJS = compileJS;
module.exports.watch = watchFiles;
