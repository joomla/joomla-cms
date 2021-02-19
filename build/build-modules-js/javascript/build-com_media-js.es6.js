const { readFile, writeFile } = require('fs').promises;
const { resolve } = require('path');
const { minify } = require('terser');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const VuePlugin = require('rollup-plugin-vue');
const commonjs = require('@rollup/plugin-commonjs');

const inputJS = 'administrator/components/com_media/resources/scripts/mediamanager.es6.js';

const createMinified = async (file, contents) => {
  const mini = await minify(contents, { sourceMap: false, format: { comments: false } });
  await writeFile(file, mini.code, { encoding: 'utf8' });
};
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

  const contents = await readFile('media/com_media/js/media-manager-es5.js', { encoding: 'utf8' });
  await createMinified(resolve('media/com_media/js/media-manager-es5.min.js'), contents);
  // eslint-disable-next-line no-console
  console.log('Legacy Media Manager ready ✅');
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
                esmodules: true,
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
  const contents = await readFile('media/com_media/js/media-manager.js', { encoding: 'utf8' });
  await createMinified(resolve('media/com_media/js/media-manager.min.js'), contents);
  // eslint-disable-next-line no-console
  console.log('ES2017 Media Manager ready ✅');
  await buildLegacy(resolve('media/com_media/js/media-manager.js'));
};
