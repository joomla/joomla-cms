const watch = require('watch');
const Path = require('path');
const HandleJsFile = require('./javascript/handle-file.es6.js');
const RootPath = require('./utils/rootpath.es6.js')._();

/**
 * Debounce
 * https://gist.github.com/nmsdvid/8807205
 *
 * @param { function } callback  The callback function to be executed
 * @param { integer }  time      The time to wait before firing the callback
 * @param { integer }  interval  The interval
 */
// eslint-disable-next-line max-len, no-param-reassign, no-return-assign
const debounce = (callback, time = 250, interval) => (...args) => clearTimeout(interval, interval = setTimeout(callback, time, ...args));

module.exports.run = () => {
  watch.createMonitor(Path.join(RootPath, 'build/media_source'), (monitor) => {
    monitor.on('created', (file) => {
      if (file.match(/\.js/) && (file.match(/\.es5\.js/) || file.match(/\.es6\.js/) || file.match(/\.w-c\.es6\.js/))) {
        debounce(HandleJsFile.run(file), 300);
      }
      // @todo css and scss
    });
    monitor.on('changed', (file) => {
      if (file.match(/\.js/) && (file.match(/\.es5\.js/) || file.match(/\.es6\.js/) || file.match(/\.w-c\.es6\.js/))) {
        debounce(HandleJsFile.run(file), 300);
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
