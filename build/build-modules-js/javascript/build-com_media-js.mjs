import { resolve } from 'node:path';
import { writeFile, copyFile, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { rollup, watch } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import replace from '@rollup/plugin-replace';
import { babel } from '@rollup/plugin-babel';
import VuePlugin from 'rollup-plugin-vue';
import commonjs from '@rollup/plugin-commonjs';
import dotenv from 'dotenv';
import { minifyCode } from './minify.mjs';

dotenv.config();

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.mjs';
const isProduction = process.env.NODE_ENV !== 'DEVELOPMENT';

const buildLegacy = async (file) => {
  // eslint-disable-next-line no-console
  console.log('Building Legacy Media Manager...');

  const bundle = await rollup({
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
    .then((value) => minifyCode(value.output[0].code))
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

const mediaManager = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Media Manager ES Module...');

  const bundle = await rollup({
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
    sourcemap: !isProduction ? 'inline' : false,
    file: 'media/com_media/js/media-manager.js',
  })
    .then((value) => (isProduction ? minifyCode(value.output[0].code) : value.output[0]))
    .then((content) => {
      if (isProduction) {
        // eslint-disable-next-line no-console
        console.log('✅ ES2017 Media Manager ready');
        writeFile(resolve('media/com_media/js/media-manager.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
        return buildLegacy(resolve('media/com_media/js/media-manager.js'));
      }
      // eslint-disable-next-line no-console
      console.log('✅ ES2017 Media Manager ready');
      if (existsSync(resolve('media/com_media/js/media-manager-es5.js'))) {
        rm(resolve('media/com_media/js/media-manager-es5.js'));
      }
      if (existsSync(resolve('media/com_media/js/media-manager-es5.min.js'))) {
        rm(resolve('media/com_media/js/media-manager-es5.min.js'));
      }
      return copyFile(resolve('media/com_media/js/media-manager.js'), resolve('media/com_media/js/media-manager.min.js'));
    })
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};

const watchMediaManager = async () => {
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
  const watcher = watch({
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

export { mediaManager, watchMediaManager };
