const Promise = require('bluebird');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Recurs = require("recursive-readdir");
const Chalk = require('chalk');
const UglifyJS = require('uglify-es');
const transpileEs5 = require('./compile-es6.js');
const debounce = require('lodash.debounce');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const watches = [
  rootPath + '/' + 'media',
  rootPath + '/' + 'administrator/templates/atum/js',
  rootPath + '/' + 'templates/cassiopeia/js'
];

uglifyJs = (options, path) => {
  let folders = [];
  if (path) {
    const stats = fs.lstatSync(rootPath + '/' + path);

    if (!stats.isDirectory()) {
      // @todo: allow to compile single file
      throw new Error ('Path should be a directory: ' + path);
    }

    folders.push(rootPath + '/' + path);
  } else {
    folders = [
      rootPath + '/' + 'media',
      rootPath + '/' + 'administrator/templates/atum/js',
      rootPath + '/' + 'templates/cassiopeia/js'
    ];
  }

  // Loop to get some text for the packgage.json
  folders.forEach((folder) => {
    Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf']).then(
      (files) => {
        files.forEach((file) => {
            if (file.match(/.es6.js/)) {
              // Transpile the file
              transpileEs5.compileFile(file)
            }

            if (file.match(/.js/) && !file.toLowerCase().match(/license/)) {
              // Write the file
              fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, "utf8")).code, {encoding: "utf8"});
            }

          },
          (error) => {
            console.error("something exploded", error);
          }
        );
      });
  });
};

watchFiles = function(options, folders, compileFirst = false) {
  folders = folders || watches;

  if (compileFirst) {
    uglifyJs(options);
  }

  folders.forEach((folder) => {
    Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf']).then(
      (files) => {
        files.forEach((file) => {
            if (file.match(/.js/)) {
              fs.watchFile(file, () => {
                console.log('File: ' + file + ' changed.');
                debounce(() => {
                  if (file.match(/.es6.js/)) {
                    // Transpile the file
                    transpileEs5.compileFile(file)
                  }
                  fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, "utf8")).code, {encoding: "utf8"});
                }, 150)();

                console.log(Chalk.bgYellow(file + ' was updated.'));
              });
            }
          },
          (error) => {
            console.error("something exploded", error);
          }
        );
      });
  });

  console.log('Now watching JS files...');
};

ujs = (options, path) => {
  Promise.resolve()
  // Compile the scss files
    .then(() => uglifyJs(options, path))

    // Handle errors
    .catch((err) => {
      console.error(Chalk.red(err));
      process.exit(-1);
    });
};

module.exports.js = ujs;
module.exports.watch = watchFiles;
