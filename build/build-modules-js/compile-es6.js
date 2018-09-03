const fs = require('fs');
const babel = require('babel-core');
const UglifyJS = require('uglify-es');
const os = require('os');

const headerText = `PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.`;

const babelOptions = {
  plugins: [
    ['add-header-comment', { header: [headerText] }],
  ],
};

/**
 * Compiles es6 files to es5.
 * @param filePath
 */
const compileFile = (filePath) => {
  babel.transformFile(filePath, babelOptions, (error, result) => {
    if (error) {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    }

    const fileName = filePath.slice(0, -7);
    console.log(`Compiling: ${fileName.replace('/build/media_src/', '/media/')}.js`);
    fs.writeFile(
      `${fileName.replace('/build/media_src/', '/media/').replace('\\build\\media_src\\', '\\media\\')}.js`,
      result.code + os.EOL,
      (fsError) => {
        if (fsError) {
          // eslint-disable-next-line no-console
          console.error(`${fsError}`);
          process.exit(1);
        }
      }
    );
    // Also write the minified
    fs.writeFile(
      `${fileName.replace('/build/media_src/', '/media/').replace('\\build\\media_src\\', '\\media\\')}.min.js`,
      UglifyJS.minify(result.code).code + os.EOL,
      (fsError) => {
        if (fsError) {
          // eslint-disable-next-line no-console
          console.error(`${fsError}`);
          process.exit(1);
        }
      }
    );
  });
};

module.exports.compileFile = compileFile;
