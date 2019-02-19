const Fs = require('fs');
const Path = require('path');

/**
 * Method to list all files in a directory recursively in a synchronous fashion
 *
 * @param {string} dir      the name of the folder to be recurse'd
 * @param {array}  filelist the array with all the files
 */
const walkSync = (dir, filelist) => {
  if (!Array.isArray(filelist)) {
    // eslint-disable-next-line no-console
    console.log('Method `walkSync` expects second parameter to be an array!');
    process.env.exit(1);
  }

  const files = Fs.readdirSync(dir);
  // eslint-disable-next-line no-param-reassign
  filelist = filelist || [];

  files.forEach((file) => {
    if (Fs.statSync(Path.join(dir, file)).isDirectory()) {
      // eslint-disable-next-line no-param-reassign
      filelist = walkSync(Path.join(dir, file), filelist);
    } else {
      // eslint-disable-next-line no-param-reassign
      filelist.push(Path.join(dir, file));
    }
  });
  return filelist;
};

module.exports.run = walkSync;
