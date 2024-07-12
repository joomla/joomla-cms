import { basename, resolve } from 'node:path';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import { babel } from '@rollup/plugin-babel';
import commonjs from '@rollup/plugin-commonjs';
import { minifyFile } from './minify.mjs';

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
const handleESMToLegacy = async (file) => {
  const bundleLegacy = await rollup({
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

    minifyFile(resolve(`${file.replace(/\.js$/, '')}-es5.js`));
  });
};

export { handleESMToLegacy };
