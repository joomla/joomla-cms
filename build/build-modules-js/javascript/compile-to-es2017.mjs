import { writeFile } from 'node:fs/promises';
import { basename, sep, resolve } from 'node:path';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import { babel } from '@rollup/plugin-babel';
import { minifyCode } from './minify.mjs';
import { handleESMToLegacy } from './compile-to-es5.mjs';

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
export const handleESMFile = async (file) => {
  const newPath = file.replace(/\.mjs$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const bundle = await rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({
        preferBuiltins: false,
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
              bugfixes: true,
              loose: true,
            },
          ],
        ],
      }),
    ],
    external: [],
  });

  bundle.write({
    format: 'es',
    sourcemap: false,
    file: resolve(`${newPath}.js`),
  })
    .then((value) => minifyCode(value.output[0].code))
    .then((content) => {
      // eslint-disable-next-line no-console
      console.log(`✅ ES2017 file: ${basename(file).replace('.mjs', '.js')}: transpiled`);

      return writeFile(resolve(`${newPath}.min.js`), content.code, { encoding: 'utf8', mode: 0o644 });
    })
    .then(() => handleESMToLegacy(resolve(`${newPath}.js`)))
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};
