const chokidar = require('chokidar');
const {
  join, extname, basename, dirname,
} = require('path');
const { handleESMFile } = require('./javascript/compile-to-es2017.es6.js');
const { handleES5File } = require('./javascript/handle-es5.es6.js');
const { handleScssFile } = require('./stylesheets/handle-scss.es6.js');
const { handleCssFile } = require('./stylesheets/handle-css.es6.js');
const { debounce } = require('./utils/debounce.es6.js');

const RootPath = process.cwd();

const processFile = (file) => {
  if (extname(file) === '.js' && !dirname(file).startsWith(join(RootPath, 'build/media_source/vendor/bootstrap/js'))) {
    if (file.match(/\.w-c\.es6\.js$/) || file.match(/\.es6\.js$/)) {
      debounce(handleESMFile(file), 300);
    }
    if (file.match(/\.es5\.js/)) {
      debounce(handleES5File(file), 300);
    }
  }

  if (extname(file) === '.scss' && !basename(file).startsWith('_')) {
    debounce(handleScssFile(file), 300);
  }
  if (extname(file) === '.css') {
    debounce(handleCssFile(file), 300);
  }
};

module.exports.watching = (path) => {
  const watchingPath = path ? join(RootPath, path) : join(RootPath, 'build/media_source');
  const watcher = chokidar.watch(watchingPath, {
    ignored: /(^|[/\\])\../, // ignore dotfiles
    persistent: true,
  });

  watcher
    .on('add', (file) => processFile(file))
    .on('change', (file) => processFile(file));
  // @todo Handle this case as well
  // .on('unlink', path => log(`File ${path} has been removed`));
};
