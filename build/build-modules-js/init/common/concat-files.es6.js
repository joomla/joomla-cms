const { readFile, writeFile, existsSync } = require('fs-extra');

const RootPath = process.cwd();

/**
 * Method to concatenate some files
 *
 * @param {array}  files   the array of files to be be concatenated
 * @param {string} output  the name of the output file
 *
 * @returns {void}
 */
module.exports.concatFiles = async (files, output) => {
  const promises = [];

  // eslint-disable-next-line no-restricted-syntax
  for (const file of files) {
    if (existsSync(`${RootPath}/${file}`)) {
      promises.push(readFile(`${RootPath}/${file}`, { encoding: 'utf8' }));
    }
  }

  const res = await Promise.all(promises);

  await writeFile(`${RootPath}/${output}`, res.join(' '), { encoding: 'utf8', mode: 0o644 });
};
