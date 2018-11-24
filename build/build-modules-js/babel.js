/**
 * Helper fn creates dir
 *
 * @param targetDir
 * @param isRelativeToScript
 * @returns {string}
 */
const Babel = require('@babel/core');
const Fs = require('fs');

module.exports.run = (fileContents, settings, output) => {
    Babel.transform(fileContents, settings, (error, result) => {
        if (error) {
            // eslint-disable-next-line no-console
            console.error(`${error}`);
            process.exit(1);
        }


        Fs.writeFile(
            output,
            result.code, // + os.EOL
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
