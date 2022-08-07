const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const rtlcss = require('rtlcss');
const { writeFile } = require('fs').promises;
const { ensureDir } = require('fs-extra');
const { dirname, sep } = require('path');
const Postcss = require('postcss');
const { compileAsync } = require('sass');

module.exports.handleScssFile = async (file) => {
  const cssFile = file.replace(`${sep}scss${sep}`, `${sep}css${sep}`)
    .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)
    .replace('.scss', '.css');

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile), {});
  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile.replace('.css', '.min.css')), {});

  const plugins = [Autoprefixer];
  if (cssFile.endsWith('-rtl.css')) plugins.push(rtlcss);

  return compileAsync(file)
    .then((compiled) => Postcss(plugins).process(compiled.css.toString(), { from: undefined }))
    .then((res) => {
      writeFile(cssFile, res.css, { encoding: 'utf8', mode: 0o644 });
      return Postcss([CssNano]).process(res.css, { from: undefined });
    })
    .then((cssMin) => writeFile(cssFile.replace('.css', '.min.css'), cssMin.css, { encoding: 'utf8', mode: 0o644 }))
    // eslint-disable-next-line no-console
    .then(() => console.log(`SCSS File compiled: ${cssFile} ✅ `))
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(error.formatted);
      process.exit(1);
    });
};
