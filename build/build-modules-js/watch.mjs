import {
  join, extname, basename, dirname,
} from 'node:path';
import chokidar from 'chokidar';

import { handleESMFile } from './javascript/compile-to-es2017.mjs';
import { handleES5File } from './javascript/handle-es5.mjs';
import { handleScssFile } from './stylesheets/handle-scss.mjs';
import { handleCssFile } from './stylesheets/handle-css.mjs';
import { debounce } from './utils/debounce.mjs';

const RootPath = process.cwd();

const processFile = (file) => {
  if (extname(file) === '.js' && !dirname(file).startsWith(join(RootPath, 'build/media_source/vendor/bootstrap/js'))) {
    if ((file.endsWith('.w-c.es6.js') || file.endsWith('.es6.js')) && !file.startsWith('_')) {
      debounce(handleESMFile(file), 300);
    }
    if (file.endsWith('.es5..js')) {
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

export const watching = (path) => {
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
