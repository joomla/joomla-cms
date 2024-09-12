/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

const { access, writeFile } = require('fs').promises;
const { constants } = require('fs');
const { basename, sep, resolve } = require('path');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const LightningCSS = require('lightningcss');
const { renderSync } = require('sass-embedded');
const { minifyJsCode } = require('./minify.es6.js');
const { getPackagesUnderScope } = require('../init/common/resolve-package.es6.js');

function esmOrIife(file) {
  if (file.endsWith('core.es6.js') || file.endsWith('validate.es6.js')) {
    return 'iife';
  }
  return 'es';
}

const getWcMinifiedCss = async (file) => {
  let scssFileExists = false;
  const scssFile = file.replace(`${sep}js${sep}`, `${sep}scss${sep}`).replace(/\.w-c\.es6\.js$/, '.scss');
  try {
    // eslint-disable-next-line no-bitwise
    await access(scssFile, constants.R_OK | constants.W_OK);

    scssFileExists = true;
  } catch { /* nothing */ }

  /// {{CSS_CONTENTS_PLACEHOLDER}}
  if (scssFileExists) {
    let compiled;
    try {
      compiled = renderSync({ file: scssFile });
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error(`${error.column}
                    ${error.message}
                    ${error.line}`);
    }

    if (typeof compiled === 'object' && compiled.css) {
      const { code } = LightningCSS.transform({
        code: Buffer.from(compiled.css.toString()),
        minify: true,
      });
      return code;
    }
  }

  return '';
};

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
module.exports.handleESMFile = async (file) => {
  const newPath = file.replace(/\.w-c\.es6\.js$/, '').replace(/\.es6\.js$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const minifiedCss = await getWcMinifiedCss(file);

  // Make sure externals are collected
  collectExternals();

  const bundle = await rollup.rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({ preferBuiltins: false }),
      replace({
        preventAssignment: true,
        CSS_CONTENTS_PLACEHOLDER: minifiedCss,
        delimiters: ['{{', '}}'],
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
    format: esmOrIife(file),
    sourcemap: false,
    file: resolve(`${newPath}.js`),
  })
    .then((value) => minifyJsCode(value.output[0].code))
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
