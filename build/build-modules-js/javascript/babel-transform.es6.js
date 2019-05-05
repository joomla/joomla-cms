const Babel = require('@babel/core');
const Fs = require('fs');
const Path = require('path');
const MakeDir = require('../utils/make-dir.es6.js');

/**
 *
 * @param fileContents  the content of the file to be transpiled
 * @param settings      the settings for the transpiler
 * @param output        the full pat + filename + extension of the trnspiled file
 */
module.exports.run = (fileContents, settings, output) => {
  Babel.transform(fileContents, settings, (error, result) => {
    if (error) {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    }

    // Ensure the folder exists or create it
    MakeDir.run(Path.dirname(output));

    Fs.writeFile(
      output,
      result.code, // + os.EOL
      (fsError) => {
        if (fsError) {
          // eslint-disable-next-line no-console
          console.error(`${fsError}`);
          process.exit(1);
        }
      },
    );
  });
};
