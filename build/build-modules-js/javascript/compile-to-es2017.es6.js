/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

const { access, writeFile } = require('fs').promises;
const { createReadStream } = require('fs');
const readline = require('readline');
const { constants } = require('fs');
const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const { basename, sep, resolve } = require('path');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const Postcss = require('postcss');
const { renderSync } = require('sass-embedded');
const { minifyJsCode } = require('./minify.es6.js');

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
      return Postcss([Autoprefixer(), CssNano()])
        .process(compiled.css.toString(), { from: undefined });
    }
  }

  return '';
};

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMFile = async (file) => {
  const newPath = file.replace(/\.w-c\.es6\.js$/, '').replace(/\.es6\.js$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const minifiedCss = await getWcMinifiedCss(file);

  // Check the file header for special options
  let shouldResolveImports = true;
  await new Promise((r) => {
    let i = 0;
    let closed = false;
    const lineReader = readline.createInterface({ input: createReadStream(file) });
    lineReader.on('line', (line) => {
      i += 1;

      if (line.indexOf('@build-no-import-resolve') !== -1) {
        shouldResolveImports = false;
      }

      if (i >= 10 && !closed) {
        lineReader.close();
        closed = true;
      }
    });
    lineReader.on('close', () => {
      closed = true;
      r();
    });
  });

  const bundle = await rollup.rollup({
    input: resolve(file),
    plugins: [
      (shouldResolveImports ? nodeResolve({ preferBuiltins: false }) : null),
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
    external: [],
  });

  bundle.write({
    format: 'es',
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
