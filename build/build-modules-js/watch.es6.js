const watch = require('watch');
const { join, extname } = require('path');
const { handleWCFile } = require('./javascript/compile-w-c.es6.js');
const { handleESMFile } = require('./javascript/compile-es6.es6.js');
const { handleES5File } = require('./javascript/handle-es5.es6.js');

const RootPath = process.cwd();

/**
 * Debounce
 * https://gist.github.com/nmsdvid/8807205
 *
 * @param { function } callback  The callback function to be executed
 * @param { int }  time      The time to wait before firing the callback
 * @param { int }  interval  The interval
 */
// eslint-disable-next-line max-len, no-param-reassign, no-return-assign
const debounce = (callback, time = 250, interval) => (...args) => clearTimeout(interval, interval = setTimeout(callback, time, ...args));

module.exports.watching = () => {
  watch.createMonitor(join(RootPath, 'build/media_source'), (monitor) => {
    monitor.on('created', (file) => {
      if (extname(file) === '.js') {
        if (file.match(/\.w-c\.es6\.js/)) {
          debounce(handleWCFile(file), 300);
        }
        if (file.match(/\.es6\.js/)) {
          debounce(handleESMFile(file), 300);
        }
        if (file.match(/\.es5\.js/)) {
          debounce(handleES5File(file), 300);
        }
      }

      // @todo css and scss
    });
    monitor.on('changed', (file) => {
      if (extname(file) === '.js') {
        if (file.match(/\.w-c\.es6\.js/)) {
          debounce(handleWCFile(file), 300);
        }
        if (file.match(/\.es6\.js/)) {
          debounce(handleESMFile(file), 300);
        }
        if (file.match(/\.es5\.js/)) {
          debounce(handleES5File(file), 300);
        }
      }
      // @todo css and scss
    });
    monitor.on('removed', (file) => {
      // Handle this case as well
      // eslint-disable-next-line no-console
      console.log(file);
    });
  });
};
