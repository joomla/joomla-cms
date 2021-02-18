const { readFile, writeFile } = require('fs').promises;
const FsExtra = require('fs-extra');
const { dirname, sep } = require('path');
const { minify } = require('terser');

module.exports.handleES5File = async (file) => {
  if (file.match(/\.es5\.js$/)) {
    // ES5 file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    await FsExtra.ensureDir(dirname(file).replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`));
    await FsExtra.copy(file, file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.js'));
    const fileContent = await readFile(file, { encoding: 'utf8' });
    const content = await minify(fileContent, { sourceMap: false, format: { comments: false } });
    await writeFile(file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.min.js'), content.code, { encoding: 'utf8' });
    // eslint-disable-next-line no-console
    console.log(`Es5 file copied/minified: ${file}`);
  }
};
