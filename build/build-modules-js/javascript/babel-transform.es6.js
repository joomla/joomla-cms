const Babel = require('@babel/core');
const Fs = require('fs');
const FsExtra = require('fs-extra');
const Path = require('path');

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
    FsExtra.mkdirsSync(Path.dirname(output), {});

    Fs.writeFile(
      output,
      result.code, // + os.EOL
      { encoding: 'utf8' },
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
