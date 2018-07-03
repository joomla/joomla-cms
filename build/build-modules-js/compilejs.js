const Promise = require('bluebird');
const fs = require('fs');
// const fsExtra = require('fs-extra');
const Recurs = require('recursive-readdir');
const chalk = require('chalk');
const UglifyJS = require('uglify-es');
const transpileEs5 = require('./compile-es6.js');
const debounce = require('lodash.debounce');
const rootPath = require('./rootpath.js')._();

const watches = [
  `${rootPath}/media`,
  `${rootPath}/administrator/templates/atum/js`,
  `${rootPath}/templates/cassiopeia/js`,
];

const uglifyJs = (options, path) => {
  let folders = [];
  if (path) {
    const stats = fs.lstatSync(`${rootPath}/${path}`);

    if (!stats.isDirectory()) {
      // @todo: allow to compile single file
      throw new Error(`Path should be a directory: ${path}`);
    }

    folders.push(`${rootPath}/${path}`);
  } else {
    folders = [
      `${rootPath}/media`,
      `${rootPath}/administrator/templates/atum/js`,
      `${rootPath}/templates/cassiopeia/js`,
    ];
  }

  // Loop to get some text for the packgage.json
  folders.forEach((folder) => {
    Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf']).then(
      (files) => {
        files.forEach(
            (file) => {
            if (file.match(/.es6.js/)) {
              // Transpile the file
              transpileEs5.compileFile(file);
              fs.writeFileSync(file.replace('.es6.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
            }

            if (file.match(/.js/) && !file.toLowerCase().match(/license/)) {
              // Write the file
              fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
            }
          },
          (error) => {
            // eslint-disable-next-line no-console
            console.error(`${chalk.red('something exploded', error)}`);
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
      Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf']).then(
        (files) => {
          files.forEach(
            (file) => {
              if (file.match(/.js/)) {
                fs.watchFile(file, () => {
                  // eslint-disable-next-line no-console
                  console.warn(`${chalk.grey(`File: ${file} changed.`)}`);
                  debounce(() => {
                    if (file.match(/.es6.js/)) {
                      // Transpile the file
                      transpileEs5.compileFile(file);
                      fs.writeFileSync(file.replace('.es6.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
                    }
                    fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
                  }, 150)();

                  // eslint-disable-next-line no-console
                  console.log(chalk.bgYellow(`${file} was updated.`));
                });
              }
            },
            (error) => {
              // eslint-disable-next-line no-console
              console.error(chalk.red('something exploded', error));
            },
          );
        }
      );
    }
  );

  // eslint-disable-next-line no-console
  console.log(chalk.magenta('Now watching JS files...'));
};

const compile = (options, path) => {
  Promise.resolve()
  // Compile the scss files
    .then(() => uglifyJs(options, path))

  // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(chalk.red(err));
      process.exit(-1);
    });
};

module.exports.compile = compile;
module.exports.watch = watchFiles;
