const { ensureDir } = require('fs-extra');
const {
  lstat, readdir, readFile, writeFile, unlink,
} = require('fs').promises;
const { basename, sep, resolve } = require('path');
const { minify } = require('terser');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMToLegacy = async (file) => {
  // eslint-disable-next-line no-console
  console.log(`Building Legacy: ${basename(file).replace('.js', '-es5.js')}...`);

  const bundleLegacy = await rollup.rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({
        preferBuiltins: false,
      }),
      replace({
        'process.env.NODE_ENV': '\'production\'',
      }),
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
                chrome: '58',
                ie: '11',
              },
              loose: true,
              bugfixes: true,
              modules: false,
            },
          ],
        ],
      }),
    ],
    external: [],
  });

  await bundleLegacy.write({
    format: 'es',
    sourcemap: false,
    file: resolve(`${file.replace(/\.js$/, '')}-es5.js`),
  });
};
