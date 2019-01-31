const watch = require('watch');
const HandleJsFile = require('./javascript/handle-file.es6.js');
const Path = require('path');
const RootPath = require('./utils/rootpath.es6.js')._();

/**
 * Debounce
 * https://gist.github.com/nmsdvid/8807205
 * 
 * @param { function } callback  The callback function to be executed
 * @param { integer }  time      The time to wait before firing the callback
 * @param { integer }  interval  The interval
 */
const debounce = (callback, time = 250, interval) => (...args) => clearTimeout(interval, interval = setTimeout(callback, time, ...args));

module.exports.run = (options, path) => {
  let inputPath = Path.join(RootPath, 'build/media_source');
  if (path) {
      inputPath = path;
  }
  watch.createMonitor(inputPath, (monitor) => {
    monitor.on("created", function (f, stat) {
      if (file.match(/\.js/) && (file.match(/\.es5\.js/) || file.match(/\.es6\.js/) || file.match(/\.w-c\.es6\.js/))) {
        debounce(HandleJsFile.run(file), 300)
      }
      // @todo css and scss
    });
    monitor.on("changed", function (file, curr, prev) {
      if (file.match(/\.js/) && (file.match(/\.es5\.js/) || file.match(/\.es6\.js/) || file.match(/\.w-c\.es6\.js/))) {
        debounce(HandleJsFile.run(file), 300)
      }
      // @todo css and scss
    });
    monitor.on("removed", function (f, stat) {
      // Handle this case as well
    })
  });
};
