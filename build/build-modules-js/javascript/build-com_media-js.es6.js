const { resolve } = require('path');
const { writeFile } = require('fs').promises;
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const VuePlugin = require('rollup-plugin-vue');
const commonjs = require('@rollup/plugin-commonjs');
const { minifyJsCode } = require('./minify.es6.js');

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.es6.js';

const buildLegacy = async (file) => {
  // eslint-disable-next-line no-console
  console.log('Building Legacy Media Manager...');

  const bundle = await rollup.rollup({
    input: file,
    plugins: [
      nodeResolve(),
      commonjs(),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              corejs: '3.8',
              useBuiltIns: 'usage',
              targets: {
                ie: '11',
              },
              loose: true,
              bugfixes: true,
              modules: false,
              ignoreBrowserslistConfig: true,
            },
          ],
        ],
      }),
    ],
    external: [],
  });

  await bundle.write({
    format: 'iife',
    sourcemap: false,
    name: 'JoomlaMediaManager',
    file: 'media/com_media/js/media-manager-es5.js',
  })
    .then((value) => minifyJsCode(value.output[0].code))
    .then((content) => {
      // eslint-disable-next-line no-console
      console.log('✅ Legacy Media Manager ready');
      return writeFile(resolve('media/com_media/js/media-manager-es5.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
    })
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};

module.exports.mediaManager = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Media Manager ES Module...');

  const bundle = await rollup.rollup({
    input: resolve(inputJS),
    plugins: [
      VuePlugin({
        target: 'browser',
        css: false,
        compileTemplate: true,
        template: {
          isProduction: true,
        },
      }),
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': JSON.stringify('production'),
      }),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: [
                  '> 1%',
                  'not ie 11',
                  'not op_mini all',
                ],
              },
              loose: true,
              bugfixes: false,
              ignoreBrowserslistConfig: true,
            },
          ],
        ],
      }),
    ],
  });

  bundle.write({
    format: 'es',
    sourcemap: false,
    file: 'media/com_media/js/media-manager.js',
  })
    .then((value) => minifyJsCode(value.output[0].code))
    .then((content) => {
      // eslint-disable-next-line no-console
      console.log('✅ ES2017 Media Manager ready');

      return writeFile(resolve('media/com_media/js/media-manager.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
    })
    .then(() => buildLegacy(resolve('media/com_media/js/media-manager.js')))
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};

module.exports.watchMediaManager = async () => {
  // eslint-disable-next-line no-console
  console.log('Watching Media Manager js+vue files...');
  // eslint-disable-next-line no-console
  console.log('=========');

  const watcher = rollup.watch({
    input: resolve(inputJS),
    plugins: [
      VuePlugin({
        target: 'browser',
        css: false,
        compileTemplate: true,
        template: {
          isProduction: true,
        },
      }),
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': JSON.stringify('production'),
      }),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: [
                  '> 1%',
                  'not ie 11',
                  'not op_mini all',
                ],
              },
              loose: true,
              bugfixes: false,
              ignoreBrowserslistConfig: true,
            },
          ],
        ],
      }),
    ],
    output: [
      {
        format: 'es',
        sourcemap: false,
        file: 'media/com_media/js/media-manager.js',
      },
      {
        format: 'es',
        sourcemap: false,
        file: 'media/com_media/js/media-manager.min.js',
      },
    ],
  });

  watcher.on('event', (event) => {
    if (event.code === 'BUNDLE_END') {
      // eslint-disable-next-line no-console
      console.log(`✅ File ${event.output[0]} updated
✅ File ${event.output[1]} updated
=========`);
    }
  });
};
