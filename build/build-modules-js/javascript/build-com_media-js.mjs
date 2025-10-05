import { resolve } from 'node:path';

import { rolldown as rollup, watch } from 'rolldown';
import { babel } from '@rollup/plugin-babel';
import VuePlugin from 'rollup-plugin-vue';
import vue from '@vitejs/plugin-vue';

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.es6.js';
const isProduction = process.env.NODE_ENV !== 'DEVELOPMENT';

export const mediaManager = async () => {
  console.log('Building Media Manager ES Module...');

  const bundle = await rollup({
    input: resolve(inputJS),
    define: {
      //'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
      __VUE_OPTIONS_API__: JSON.stringify(isProduction),
      __VUE_PROD_DEVTOOLS__: JSON.stringify(isProduction),
      __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(isProduction),
    },
    plugins: [
      vue({
        include: /.*\.vue$/,
        isProduction: isProduction,
        css: false,
        exposeFilename: false,
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
                browsers: ['baseline widely available'],
              },
              loose: true,
              bugfixes: true,
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
  }).catch((error) => { throw new Error(error); });

  bundle.write({
    format: 'es',
    minify: true,
    sourcemap: !isProduction ? 'inline' : false,
    file: 'media/com_media/js/media-manager.min.js',
  }).catch((error) => { throw new Error(error); });

  // closes the bundle
  await bundle.close();
};

export const watchMediaManager = async () => {
  console.log('Watching Media Manager js+vue files...');
  console.log('=========');
  const watcher = watch({
    input: resolve(inputJS),
    define: {
      //'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
      __VUE_OPTIONS_API__: JSON.stringify(isProduction),
      __VUE_PROD_DEVTOOLS__: JSON.stringify(isProduction),
      __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(isProduction),
    },
    plugins: [
      vue({
        include: /.*\.vue$/,
        isProduction: isProduction,
        css: false,
        exposeFilename: false,
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
                browsers: ['baseline widely available'],
              },
              loose: true,
              bugfixes: true,
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
    if (error) throw new Error(error);
    if (code === 'BUNDLE_END') console.log('Files updated ✅');
  });
};
