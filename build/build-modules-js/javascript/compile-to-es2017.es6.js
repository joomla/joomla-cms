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
const { handleESMToLegacy } = require('./compile-to-es5.es6.js');

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

/**
 * Compiles es6 files to es5.
 *
 * @param file the full path to the file + filename + extension
 */
module.exports.handleESMFile = async (file) => {
  const newPath = file.replace(/\.w-c\.es6\.js$/, '').replace(/\.es6\.js$/, '').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  const minifiedCss = await getWcMinifiedCss(file);
  const bundle = await rollup.rollup({
    input: resolve(file),
    plugins: [
      nodeResolve({
        preferBuiltins: false,
      }),
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
    .then(() => handleESMToLegacy(resolve(`${newPath}.js`)))
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error);
    });

  // closes the bundle
  await bundle.close();
};
