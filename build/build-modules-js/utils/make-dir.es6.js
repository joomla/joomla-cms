/**
 * Helper fn creates dir
 *
 * @param targetDir
 * @param isRelativeToScript
 * @returns {string}
 */
const Fs = require('fs');
const Path = require('path');

module.exports.run = (targetDir, { isRelativeToScript = false } = {}) => {
  const { sep } = Path;
  const initDir = Path.isAbsolute(targetDir) ? sep : '';
  const baseDir = isRelativeToScript ? __dirname : '.';

  return targetDir.split(sep).reduce((parentDir, childDir) => {
    const curDir = Path.resolve(baseDir, parentDir, childDir);
    try {
      Fs.mkdirSync(curDir);
    } catch (err) {
      if (err.code === 'EEXIST') { // curDir already exists!
        return curDir;
      }

      // To avoid `EISDIR` error on Mac and `EACCES`-->`ENOENT` and `EPERM` on Windows.
      if (err.code === 'ENOENT') { // Throw the original parentDir error on curDir `ENOENT` failure.
        throw new Error(`EACCES: permission denied, mkdir '${parentDir}'`);
      }

      const caughtErr = ['EACCES', 'EPERM', 'EISDIR'].indexOf(err.code) > -1;
      if (!caughtErr || (caughtErr && curDir === Path.resolve(targetDir))) {
        throw new Error(`${err}`);
      }
    }

    return curDir;
  }, initDir);
};
