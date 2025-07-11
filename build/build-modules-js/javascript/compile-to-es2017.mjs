/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

import { writeFile } from 'node:fs/promises';
import { basename, sep, resolve } from 'node:path';

import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import { babel } from '@rollup/plugin-babel';

import { minifyCode } from './minify.mjs';
import { getPackagesUnderScope } from '../init/common/resolve-package.cjs';

// List of external modules that should not be resolved by rollup
const externalModules = [];
const collectExternals = () => {
  if (externalModules.length) {
    return;
  }

  // Joomla and Vendor modules
  externalModules.push(
    'cropper-module',
    'codemirror',
    'joomla.dialog',
    'editor-api',
    'editor-decorator',
    'sa11y',
    'sa11y-lang',
  );

  // Codemirror modules
  const cmModules = getPackagesUnderScope('@codemirror');
  if (cmModules) {
    externalModules.push(...cmModules);
  }
  const lezerModules = getPackagesUnderScope('@lezer');
  if (lezerModules) {
    externalModules.push(...lezerModules);
  }
};

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
export const handleESMFile = async (file) => {
  const newPath = file.replace(/\.w-c\.es6\.js$/, '').replace(/\.es6\.js$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);

  // Make sure externals are collected
  collectExternals();

  const bundle = await rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({ preferBuiltins: false }),
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
              bugfixes: true,
              loose: true,
            },
          ],
        ],
      }),
    ],
    external: externalModules,
  });

  bundle.write({
    format: file.endsWith('core.es6.js') ? 'iife' : 'es',
    sourcemap: false,
    file: resolve(`${newPath}.js`),
  })
    .then((value) => minifyCode(value.output[0].code))
    .then((content) => {
      // eslint-disable-next-line no-console
      console.log(`✅ ES2017 file: ${basename(file).replace('.es6.js', '.js')}: transpiled`);

      return writeFile(resolve(`${newPath}.min.js`), content.code, { encoding: 'utf8', mode: 0o644 });
    })
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};
