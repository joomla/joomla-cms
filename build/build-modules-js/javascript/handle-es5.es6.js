const FsExtra = require('fs-extra');
const { basename, dirname, sep } = require('path');
const { minifyJs } = require('./minify.es6.js');

module.exports.handleES5File = async (file) => {
  if (file.match(/\.es5\.js$/)) {
    // ES5 file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    await FsExtra.ensureDir(dirname(file).replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`));
    await FsExtra.copy(file, file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.js'), { preserveTimestamps: true });
    // eslint-disable-next-line no-console
    console.log(`✅ Legacy js file: ${basename(file)}: copied`);

    minifyJs(file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.js'));
  }
};
