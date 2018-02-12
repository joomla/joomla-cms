const glob = require('glob');
const fs = require('fs');
const babel = require('babel-core');

const pattern = './**/*.es6.js';
const options = {
  ignore: './node_modules/**',
};

/**
 * Compiles es6 files to es5.
 * @param filePath
 */
const compileFile = (filePath) => {
  babel.transformFile(filePath, {}, (error, result) => {
    if (error) process.exit(1);
    const fileName = filePath.slice(0, -7);
    fs.writeFile(`${fileName}.js`, result.code);
  });
};

// Compile all files of the given pattern
glob(pattern, options, (error, files) => {
  if (error) process.exit(1);
  files.forEach(compileFile);
});

