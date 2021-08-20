const { resolve } = require('path');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const VuePlugin = require('rollup-plugin-vue');
const commonjs = require('@rollup/plugin-commonjs');
const { minifyJs } = require('./minify.es6.js');

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

  await bundle.write({
    format: 'es',
    sourcemap: false,
    file: 'media/com_media/js/media-manager.js',
  });

  // closes the bundle
  await bundle.close();

  // eslint-disable-next-line no-console
  console.log('✅ ES2017 Media Manager ready');
  minifyJs('media/com_media/js/media-manager.js');
  return buildLegacy(resolve('media/com_media/js/media-manager.js'));
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
      console.log(`File ${event.output[0]} updated ✅
File ${event.output[1]} updated ✅
=========`);
    }
  });
};
