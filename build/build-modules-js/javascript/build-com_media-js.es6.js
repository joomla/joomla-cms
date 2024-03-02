const { resolve } = require('path');
const { writeFile, copyFile } = require('fs').promises;
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const VuePlugin = require('rollup-plugin-vue');
const commonjs = require('@rollup/plugin-commonjs');
const { minifyJsCode } = require('./minify.es6.js');
require('dotenv').config();

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.es6.js';
const isProduction = process.env.NODE_ENV !== 'DEVELOPMENT';

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
        __VUE_PROD_DEVTOOLS__: !isProduction,
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
                  'not op_mini all',
                  /** https://caniuse.com/es6-module */
                  'chrome >= 61',
                  'safari >= 11',
                  'edge >= 16',
                  'Firefox >= 60',
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
    sourcemap: !isProduction ? 'inline' : false,
    file: 'media/com_media/js/media-manager.js',
  })
    .then((value) => (isProduction ? minifyJsCode(value.output[0].code) : value.output[0]))
    .then((content) => {
      if (isProduction) {
        // eslint-disable-next-line no-console
        console.log('✅ ES2017 Media Manager ready');
        return writeFile(resolve('media/com_media/js/media-manager.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
      }
      // eslint-disable-next-line no-console
      console.log('✅ ES2017 Media Manager ready');
      return copyFile(resolve('media/com_media/js/media-manager.js'), resolve('media/com_media/js/media-manager.min.js'));
    })
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
                  'not op_mini all',
                  /** https://caniuse.com/es6-module */
                  'chrome 61',
                  'safari 11',
                  'edge 16',
                  'Firefox 60',
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
