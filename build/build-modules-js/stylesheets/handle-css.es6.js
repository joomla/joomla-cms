const {
  copy, readFile, writeFile, ensureDir,
} = require('fs-extra');
const { dirname, sep } = require('path');
const Postcss = require('postcss');
const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');

module.exports.handleCssFile = async (file) => {
  const outputFile = file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  try {
    // CSS file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    await ensureDir(dirname(outputFile), { recursive: true, mode: 0o755 });

    if (file !== outputFile) {
      await copy(file, outputFile, { preserveTimestamps: true, overwrite: true });
    }

    const content = await readFile(file, { encoding: 'utf8' });
    const cssMin = await Postcss([Autoprefixer, CssNano]).process(content, { from: undefined });

    // Ensure the folder exists or create it
    await writeFile(outputFile.replace('.css', '.min.css'), cssMin.css.toString(), { encoding: 'utf8', mode: 0o644 });

    // eslint-disable-next-line no-console
    console.log(`✅ CSS file copied/minified: ${file}`);
  } catch (err) {
    // eslint-disable-next-line no-console
    console.log(err);
  }
};
