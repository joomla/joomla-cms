const { minify } = require('terser');
const { readFile, writeFile } = require('fs-extra');
const { basename } = require('path');
/**
 * Minify a js file using Terser
 *
 * @param file
 * @returns {Promise<void>}
 */
const minifyFile = async (file) => {
  const fileContent = await readFile(file, { encoding: 'utf8' });
  const content = await minify(fileContent, { sourceMap: false, format: { comments: false } });
  await writeFile(file.replace('.js', '.min.js'), content.code, { encoding: 'utf8', mode: 0o644 });
  // eslint-disable-next-line no-console
  console.log(`✅ Legacy js file: ${basename(file)}: minified`);
};

/**
 * Minify a chunk of js using Terser
 *
 * @param code
 * @returns {Promise<void>}
 */
const minifyCode = async (code) => minify(code, { sourceMap: false, format: { comments: false } });

module.exports.minifyJs = minifyFile;
module.exports.minifyJsCode = minifyCode;
