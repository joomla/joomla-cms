const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Fs = require('fs').promises;
const FsExtra = require('fs-extra');
const { dirname, sep } = require('path');
const Postcss = require('postcss');
const Sass = require('sass-embedded');

module.exports.compile = async (file) => {
  const cssFile = file.replace(`${sep}scss${sep}`, `${sep}css${sep}`)
    .replace('.scss', '.css').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);

  let compiled;
  try {
    compiled = Sass.renderSync({ file });
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(error.formatted);
    process.exit(1);
  }

  // Auto prefixing
  const cleaner = Postcss([Autoprefixer()]);
  const res = await cleaner.process(compiled.css.toString(), { from: undefined });

  // Ensure the folder exists or create it
  await FsExtra.mkdirs(dirname(cssFile), {});
  await Fs.writeFile(
    cssFile,
    res.css.toString(),
    { encoding: 'utf8', mode: 0o644 },
  );

  const cssMin = await Postcss([CssNano]).process(res.css.toString(), { from: undefined });

  // Ensure the folder exists or create it
  FsExtra.mkdirs(dirname(cssFile.replace('.css', '.min.css')), {});
  await Fs.writeFile(
    cssFile.replace('.css', '.min.css'),
    cssMin.css.toString(),
    { encoding: 'utf8', mode: 0o644 },
  );

  // eslint-disable-next-line no-console
  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
