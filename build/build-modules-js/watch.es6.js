const Fs = require('fs');
const watch = require('watch');
const Path = require('path');
const Recurs = require('recursive-readdir');
const CompileScss = require('./stylesheets/scss-transform.es6.js');
const HandleJsFile = require('./javascript/handle-file.es6.js');
const RootPath = require('./utils/rootpath.es6.js')._();

/**
 * Debounce
 * https://gist.github.com/nmsdvid/8807205
 *
 * @param { function }  callback  The callback function to be executed
 * @param { integer  }  time      The time to wait before firing the callback
 * @param { integer  }  interval  The interval
 */
// eslint-disable-next-line max-len, no-param-reassign, no-return-assign
const debounce = (callback, time = 250, interval) => (...args) => clearTimeout(interval, interval = setTimeout(callback, time, ...args));

module.exports.run = (path) => {
  let folder = '';
  if (path) {
    const stats = Fs.lstatSync(`${RootPath}/${path}`);

    if (stats.isDirectory() && (`${RootPath}/${path}`.includes('build/media_source') || `${RootPath}/${path}`.includes('administator/templates') || `${RootPath}/${path}`.includes('/templates'))) {
      folder = `${RootPath}/${path}`;
    } else if (stats.isFile()) {
      // eslint-disable-next-line no-console
      console.error('Watch is only available for directories');
      process.exit(1);
    } else {
      // eslint-disable-next-line no-console
      console.error(`Unknown path ${path}`);
      process.exit(1);
    }
  } else {
    folder = `${RootPath}/build/media_source`;
  }

  if (folder) {
    const forFolder = folder.includes(`${RootPath}/build/media_source`) ? 'media' : 'templates';

    // eslint-disable-next-line no-console
    console.error(`Watching: ${forFolder}`);
    watch.createMonitor(folder, (monitor) => {
      monitor.on('created', (file) => {
        if (file.match(/\.js$/) && (file.match(/\.es5\.js$/) || file.match(/\.es6\.js$/) || file.match(/\.w-c\.es6\.js$/))) {
          debounce(HandleJsFile.run(file, forFolder), 300);
        }
        if (file.match(/\.scss$/) && Path.basename(file).charAt(0) !== '_') {
          // Not a partial, will compile just the file
          debounce(CompileScss.compile(file, forFolder), 300);
        } else if (file.match(/\.scss$/) && Path.basename(file).charAt(0) === '_') {
          // This is a partial we need to rebuild everything
          const mediaFiles = [];
          const templateFiles = [];
          const parts = Path.basename(file).split['/scss'];
          Recurs(`${parts[0]}/scss`, ['*.js', '*.css', '*.map', '*.svg', '*.png', '*.jpg', '*.gif', '*.ico', '*.swf', '*.json', '*.php', '*.ini', '*.xml', '*.html', '.DS_Store']).then(
            (filesRc) => {
              filesRc.forEach(
                (rFile) => {
                  if (rFile.match(/\.scss$/) && Path.basename(rFile).charAt(0) !== '_') {
                    if (forFolder === 'media') {
                      mediaFiles.push(rFile);
                    } else if (forFolder === 'templates') {
                      templateFiles.push(rFile);
                    }
                  }
                },
                (error) => {
                  // eslint-disable-next-line no-console
                  console.error(`something exploded here ${error}`);
                },
              );
            },
          )
            .then(() => {
              mediaFiles.forEach((inputFile) => {
                debounce(CompileScss.compile(inputFile, 'media'), 300);
              });
              templateFiles.forEach((inputFile) => {
                debounce(CompileScss.compile(inputFile, 'templates'), 300);
              });
            });
        }
        // @todo css
      });
      monitor.on('changed', (file) => {
        console.log(file)
        if (file.match(/\.js$/) && (file.match(/\.es5\.js$/) || file.match(/\.es6\.js$/) || file.match(/\.w-c\.es6\.js$/))) {
          debounce(HandleJsFile.run(file, forFolder), 300);
        }
        if (file.match(/\.scss$/) && Path.basename(file).charAt(0) !== '_') {
          // Not a partial, will compile just the file
          debounce(CompileScss.compile(file, forFolder), 300);
        } else if (file.match(/\.scss$/) && Path.basename(file).charAt(0) === '_') {
          // This is a partial we need to rebuild everything
          const mediaFiles = [];
          const templateFiles = [];
          const parts = Path.basename(file).split['/scss'];
          Recurs(`${parts[0]}/scss`, ['*.js', '*.css', '*.map', '*.svg', '*.png', '*.jpg', '*.gif', '*.ico', '*.swf', '*.json', '*.php', '*.ini', '*.xml', '*.html', '.DS_Store']).then(
            (filesRc) => {
              filesRc.forEach(
                (rFile) => {
                  if (rFile.match(/\.scss$/) && Path.basename(rFile).charAt(0) !== '_') {
                    if (forFolder === 'media') {
                      mediaFiles.push(rFile);
                    } else if (forFolder === 'templates') {
                      templateFiles.push(rFile);
                    }
                  }
                },
                (error) => {
                  // eslint-disable-next-line no-console
                  console.error(`something exploded here ${error}`);
                },
              );
            },
          )
            .then(() => {
              mediaFiles.forEach((inputFile) => {
                debounce(CompileScss.compile(inputFile, 'media'), 300);
              });
              templateFiles.forEach((inputFile) => {
                debounce(CompileScss.compile(inputFile, 'templates'), 300);
              });
            });
        }
        // @todo css
      });
      monitor.on('removed', (file) => {
        // Handle this case as well
        // eslint-disable-next-line no-console
        console.log(file);
      });
    });
  }

};
