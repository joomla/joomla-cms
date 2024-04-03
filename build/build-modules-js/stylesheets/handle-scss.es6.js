const rtlcss = require('rtlcss');
const { writeFile } = require('fs').promises;
const { ensureDir } = require('fs-extra');
const { dirname, sep } = require('path');
const LightningCSS = require('lightningcss');
const Sass = require('sass-embedded');

module.exports.handleScssFile = async (file) => {
  const cssFile = file.replace(`${sep}scss${sep}`, `${sep}css${sep}`)
    .replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`)
    .replace('.scss', '.css');

  let compiled;
  try {
    compiled = Sass.renderSync({ file });
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(error.formatted);
    process.exitCode = 1;
  }

  let contents = LightningCSS.transform({
    code: Buffer.from(compiled.css.toString()),
    minify: false,
  }).code;

  if (cssFile.endsWith('-rtl.css')) {
    contents = rtlcss.process(contents);
  }

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile), {});
  await writeFile(
    cssFile,
    `@charset "UTF-8";
${contents}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  const cssMin = LightningCSS.transform({
    code: Buffer.from(contents),
    minify: true,
    exclude: LightningCSS.Features.VendorPrefixes,
  });

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile.replace('.css', '.min.css')), {});
  await writeFile(
    cssFile.replace('.css', '.min.css'),
    `@charset "UTF-8";${cssMin.code}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  // eslint-disable-next-line no-console
  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
