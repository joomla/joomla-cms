const watch = require('watch');
const { join, extname } = require('path');
const { handleESMFile } = require('./javascript/compile-to-es2017.es6.js');
const { handleES5File } = require('./javascript/handle-es5.es6.js');
const { debounce } = require('./utils/debounce.es6.js');

const RootPath = process.cwd();

module.exports.watching = () => {
  watch.createMonitor(join(RootPath, 'build/media_source'), (monitor) => {
    monitor.on('created', (file) => {
      if (extname(file) === '.js') {
        if (file.match(/\.w-c\.es6\.js$/) || file.match(/\.es6\.js$/)) {
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
        if (file.match(/\.w-c\.es6\.js$/) || file.match(/\.es6\.js$/)) {
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
