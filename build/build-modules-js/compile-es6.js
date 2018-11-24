const fs = require('fs');
const path = require('path');
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
  console.log(`Compiling ES5 version for: ${path.relative(process.cwd(), filePath)}`);

  // Make sure the file has .es6.js extension
  if (filePath.indexOf('.es6.js') === -1) {
    throw new Error('File that contain ES6 code must have .es6.js extension');
  }

  babel.transformFile(filePath, babelOptions, (error, result) => {
    if (error) {
      throw error;
    }

    const dirName = path.dirname(filePath),
      fileName    = path.basename(filePath, '.es6.js'),
      destDirName = dirName.replace('/build/media_src/', '/media/'),
      destFile    = `${destDirName}/${fileName}.es5.js`,
      destFileMin = `${destDirName}/${fileName}.es5.min.js`;

    // Make sure a destination folder exists
    fs.mkdirSync(destDirName, {recursive: true});

    // Write the result
    fs.writeFile(
      destFile,
      result.code + os.EOL,
      (fsError) => {
        if (fsError) {
          throw fsError;
        }
      }
    );

    // Also write the minified version
    fs.writeFile(
      destFileMin,
      UglifyJS.minify(result.code).code + os.EOL,
      (fsError) => {
        if (fsError) {
          throw fsError;
        }
      }
    );
  });
};

module.exports.compileFile = compileFile;
