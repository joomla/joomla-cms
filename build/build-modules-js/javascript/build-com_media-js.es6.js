const { resolve } = require('path');
const { copyFile } = require('fs').promises;
const { existsSync, rm } = require('fs');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const VuePlugin = require('rollup-plugin-vue');
const commonjs = require('@rollup/plugin-commonjs');
const { minifyJs } = require('./minify.es6.js');
require('dotenv').config();

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.es6.js';
const isProduction = process.env.NODE_ENV !== 'DEVELOPMENT';

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
  });

  // closes the bundle
  await bundle.close();

  // eslint-disable-next-line no-console
  console.log('Legacy Media Manager ready ✅');

  minifyJs('media/com_media/js/media-manager-es5.js');
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
          isProduction,
        },
      }),
      nodeResolve(),
      commonjs(),
      replace({
        'process.env.NODE_ENV': JSON.stringify((process.env.NODE_ENV && process.env.NODE_ENV.toLocaleLowerCase()) || 'production'),
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: isProduction,
        preventAssignment: true,
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

  await bundle.write({
    format: 'es',
    sourcemap: false,
    file: 'media/com_media/js/media-manager.js',
  });

  // closes the bundle
  await bundle.close();

  if (isProduction) {
    // eslint-disable-next-line no-console
    console.log('✅ ES2017 Media Manager ready');
    minifyJs('media/com_media/js/media-manager.js');
    return buildLegacy(resolve('media/com_media/js/media-manager.js'));
  }
  // eslint-disable-next-line no-console
  console.log('✅ ES2017 Media Manager ready');
  copyFile('media/com_media/js/media-manager.js', 'media/com_media/js/media-manager.js');
  return '';
};

module.exports.watchMediaManager = async () => {
  if (existsSync(resolve('media/com_media/js/media-manager-es5.js'))) {
    rm(resolve('media/com_media/js/media-manager-es5.js'));
  }
  if (existsSync(resolve('media/com_media/js/media-manager-es5.min.js'))) {
    rm(resolve('media/com_media/js/media-manager-es5.min.js'));
  }
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
      commonjs(),
      replace({
        'process.env.NODE_ENV': JSON.stringify('development'),
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: true,
        preventAssignment: true,
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
        sourcemap: 'inline',
        file: 'media/com_media/js/media-manager.js',
      },
      {
        format: 'es',
        sourcemap: 'inline',
        file: 'media/com_media/js/media-manager.min.js',
      },
    ],
  });

  watcher.on('event', ({ code, result, error }) => {
    if (result) result.close();
    // eslint-disable-next-line no-console
    if (error) console.log(error);
    // eslint-disable-next-line no-console
    if (code === 'BUNDLE_END') console.log('Files updated ✅');
  });
};
