import {
  readdir, readFile, writeFile, unlink,
} from 'node:fs/promises';
import { existsSync, mkdirSync, rmSync} from 'node:fs';
import { resolve } from 'node:path';
import { transform } from 'esbuild';
import { rolldown as rollup } from 'rolldown';
import { babel } from '@rollup/plugin-babel';

import opts from '../../../package.json' with { type: 'json' };

const bsVersion = opts.dependencies.bootstrap.replace(/^\^|~/, '');
const tasks = [];
const inputFolder = 'build/media_source/vendor/bootstrap/js';
const outputFolder = 'media/vendor/bootstrap/js';

const createMinified = async (file) => {
  const initial = await readFile(resolve(outputFolder, file), {
    encoding: 'utf8',
  });
  const mini = await transform(
    initial.replace('./popper.js', `./popper.min.js?${bsVersion}`).replace('./dom.js', `./dom.min.js?${bsVersion}`).replace('./rolldown-runtime.js', `./rolldown-runtime.min.js?${bsVersion}`),
    { minify: true },
  );
  await writeFile(
    resolve(outputFolder, file),
    initial.replace('./popper.js', `./popper.js?${bsVersion}`).replace('./dom.js', `./dom.js?${bsVersion}`).replace('./rolldown-runtime.js', `./rolldown-runtime.min.js?${bsVersion}`),
    { encoding: 'utf8', mode: 0o644 },
  );
  await writeFile(resolve(outputFolder, file.replace('.js', '.min.js')), mini.code, { encoding: 'utf8', mode: 0o644 });
};

const build = async () => {
  console.log('Building ES6 Components...');

  const bundle = await rollup({
    input: resolve(inputFolder, 'index.es6.js'),
    define: {
      preventAssignment: JSON.stringify('always'),
      'process.env.NODE_ENV': JSON.stringify('production'),
    },
    plugins: [
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: ['baseline widely available'],
              },
              loose: true,
              bugfixes: true,
              ignoreBrowserslistConfig: false,
            },
          ],
        ],
      }),
    ],
  });

  await bundle.write({
    format: 'es',
    sourcemap: false,
    dir: outputFolder,
    chunkFileNames: '[name].js',
    advancedChunks: {
      groups: [
        { name: 'popper', test: (moduleId) => moduleId.includes('@popperjs/core'), priority: 2000},
        { name: 'popper', test: (moduleId) => moduleId === 'rolldown:runtime', priority: 1500 },
        { name: 'dom', test: (moduleId) => moduleId.includes('/js/src/dom/') ||
            moduleId.includes('js/src/util') ||
            moduleId.includes('js/src/base-component.js'), priority: 1000 },
        { name: 'alert', test: (moduleId) => moduleId.includes('/js/alert'), priority: 1},
        { name: 'button', test: (moduleId) => moduleId.includes('/js/button'), priority: 2},
        { name: 'carousel', test: (moduleId) => moduleId.includes('/js/carousel'), priority: 3 },
        { name: 'collapse', test: (moduleId) => moduleId.includes('/js/collapse'), priority: 4 },
        { name: 'dropdown', test: (moduleId) => moduleId.includes('/js/dropdown'), priority: 5 },
        { name: 'modal', test: (moduleId) => moduleId.includes('/js/modal'), priority: 6 },
        { name: 'offcanvas', test: (moduleId) => moduleId.includes('/js/offcanvas'), priority: 7 },
        { name: 'scrollspy', test: (moduleId) => moduleId.includes('/js/scrollspy'), priority: 8 },
        { name: 'tab', test: (moduleId) => moduleId.includes('/js/tab'), priority: 9 },
        { name: 'toast', test: (moduleId) => moduleId.includes('/js/toast'), priority: 10 },
      ],
    },
  });

  // closes the bundle
  await bundle.close();
};

export const bootstrapJs = async () => {
  if (existsSync(resolve(outputFolder))) {
    rmSync(resolve(outputFolder), { recursive: true, force: true });
    mkdirSync(resolve(outputFolder), { recursive: true, mode: 0o755 });
  }

  try {
    await build(resolve(inputFolder, 'index.es6.js'));
    await unlink(resolve(outputFolder, 'index.es6.js'));
  } catch (error) {
    throw new Error(error);
  }

  (await readdir(outputFolder)).forEach((file) => {
    tasks.push(createMinified(file));
  });

  return Promise.all(tasks).catch((er) => {
    throw new Error(er);
  });
};
