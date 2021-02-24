const { writeFile } = require('fs').promises;
const Babel = require('@babel/core');
const FsExtra = require('fs-extra');
const { dirname } = require('path');
const { minify } = require('terser');

/**
 * Create the minified file as well
 *
 * @returns {Promise<void>}
 * @param fileName {string}
 * @param data {string}
 */
const createMinified = async (fileName, data) => {
  const mini = await minify(data, { sourceMap: false, format: { comments: false } });
  await writeFile(fileName, mini.code, { encoding: 'utf8' });
};

/**
 *
 * @param fileContents  the content of the file to be transpiled
 * @param settings      the settings for the transpiler
 * @param output        the full pat + filename + extension of the transpiled file
 */
module.exports.BabelTransform = async (fileContents, settings, output) => {
  let transformedData;

  try {
    transformedData = await Babel.transform(fileContents, settings);
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(`${error}`);
    process.exit(1);
  }

  // Ensure the folder exists or create it
  await FsExtra.ensureDir(dirname(output), {});

  await writeFile(
    output,
    transformedData.code, // + os.EOL
    { encoding: 'utf8' },
    (fsError) => {
      if (fsError) {
        // eslint-disable-next-line no-console
        console.error(`${fsError}`);
        process.exit(1);
      }
    },
  );

  await createMinified(output.replace('.js', '.min.js'), transformedData.code);
};
