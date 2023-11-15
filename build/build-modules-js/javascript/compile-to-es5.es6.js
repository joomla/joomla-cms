const { basename, resolve } = require('path');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const { babel } = require('@rollup/plugin-babel');
const commonjs = require('@rollup/plugin-commonjs');
const { minifyJs } = require('./minify.es6.js');

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMToLegacy = async (file) => {
  const bundleLegacy = await rollup.rollup({
    input: resolve(file),
    plugins: [
      nodeResolve(),
      commonjs(),
      babel({
        exclude: ['node_modules/core-js/**', 'media/system/js/core.js'],
        babelHelpers: 'bundled', // runtime
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              corejs: '3.8',
              useBuiltIns: 'entry', // usage
              targets: {
                ie: '11',
              },
              loose: true,
              bugfixes: false,
              modules: false,
            },
          ],
        ],
        // plugins: ['@babel/plugin-transform-runtime'],
      }),
    ],
  });

  await bundleLegacy.write({
    format: 'iife',
    sourcemap: false,
    file: resolve(`${file.replace(/\.js$/, '')}-es5.js`),
  }).then(() => {
    // eslint-disable-next-line no-console
    console.log(`✅ ES5 file: ${basename(file).replace('.js', '-es5.js')}: transpiled`);

    minifyJs(resolve(`${file.replace(/\.js$/, '')}-es5.js`));
  });
};
