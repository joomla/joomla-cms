/**
 * JS handler
 */

import fsp from 'node:fs/promises';
import path from "node:path";
import fs from "node:fs";

import { transform } from 'esbuild';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import { babel } from '@rollup/plugin-babel';
import { getPackagesUnderScope } from '../utils/resolve-package.mjs';

// List of external modules that should not be resolved by rollup
// @TODO: Make it configurable somehow?
const externalModules = [];
const getExternalModules = async () => {
  if (externalModules.length) {
    return externalModules;
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

  return externalModules;
};

/**
 * Minify JS content
 *
 * @param   { String } content
 * @returns { Promise<String> }
 */
export const minifyJSContent = async (content = '') => transform(content, { minify: true })
  .then((result) => result.code);

/**
 * Handle JS file without extra processing. Just minification.
 *
 * @param { String } srcPath
 * @param { String } targetPath
 * @returns { Promise }
 */
export const handleJSFile = async (srcPath, targetPath) => {
  const targetFolder = path.dirname(targetPath);

  if (!fs.existsSync(targetFolder)) {
    fs.mkdirSync(targetFolder, { mode: 0o755, recursive: true });
  }

  return fsp.readFile(srcPath, { encoding: 'utf8' }).then((content) => {
    return minifyJSContent(content).then((jsMin) => {
      // Copy source
      const saveCopy = (srcPath !== targetPath) ? fsp.copyFile(srcPath, targetPath) : Promise.resolve();

      // Store minified version
      const saveMin = fsp.writeFile(
        targetPath.replace('.js', '.min.js'),
        jsMin,
        { encoding: 'utf8', mode: 0o644 }
      );

      return Promise.all([saveCopy, saveMin]);
    });
  }).catch((error) => {
    throw new Error(`Processing failed for "${srcPath}".`, { cause: error });
  });
};

/**
 * Handle JS file which requires extra processing.
 *
 * @param { String } srcPath
 * @param { String } targetPath
 * @param { string[] } externalModulesList
 * @returns { Promise }
 */
export const handleMJSFile = async (srcPath, targetPath, externalModulesList = []) => {
  const externalModules =  externalModulesList && externalModulesList.length ? externalModulesList : await getExternalModules();
  const targetFolder = path.dirname(targetPath);

  if (!fs.existsSync(targetFolder)) {
    fs.mkdirSync(targetFolder, { mode: 0o755, recursive: true });
  }

  return rollup({
    input: srcPath,
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
  }).then(( bundle) => {
    // Process and store source file
    const result = bundle.write({
      format: targetPath.endsWith('core.js') ? 'iife' : 'es',
      sourcemap: false,
      file: targetPath,
    });

    // Minify the code and store
    const saveMin = result
      .then(( value ) => minifyJSContent(value.output[0].code))
      .then(( jsMin ) => {
        return fsp.writeFile(
          targetPath.replace('.js', '.min.js'),
          jsMin,
          { encoding: 'utf8', mode: 0o644 }
        );
      });

    return saveMin.then(() => bundle.close());
  }).catch((error) => {
    throw new Error(`Processing failed for "${srcPath}".`, { cause: error });
  });
};
