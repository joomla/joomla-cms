import { writeFile } from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import rtlcss from 'rtlcss';
import { ensureDir } from 'fs-extra';
import { transform as transformCss, Features, composeVisitors } from 'lightningcss';
import { compileAsync } from 'sass-embedded';
import { urlVersioning } from './css-versioning.mjs';

const getOutputFile = (file) => file.replace(`${sep}scss${sep}`, `${sep}css${sep}`).replace('.scss', '.css').replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);

export const handleScssFile = async (file) => {
  let contents;
  const cssFile = getOutputFile(file);

  try {
    const { css } = await compileAsync(file);
    contents = css.toString();
  } catch (error) {
    throw new Error(error.formatted);
  }

  if (cssFile.endsWith('-rtl.css')) {
    contents = rtlcss.process(contents);
  }

  // To preserve the licence the comment needs to start at the beginning of the file
  contents = contents.startsWith('@charset "UTF-8";\n') ? contents.replace('@charset "UTF-8";\n', '') : contents;

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile), {});

  const { code: css } = transformCss({
    code: Buffer.from(contents),
    minify: false,
    exclude: Features.VendorPrefixes,
    visitor: composeVisitors([urlVersioning(file)]), // Adds a hash to the url() parts of the static css
  });

  // Save optimized css file
  await writeFile(
    cssFile,
    contents.startsWith('@charset "UTF-8";')
      ? css
      : `@charset "UTF-8";
${css}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  const { code: cssMin } = transformCss({
    code: Buffer.from(contents),
    minify: true,
    exclude: Features.VendorPrefixes,
    visitor: composeVisitors([urlVersioning(cssFile)]), // Adds a hash to the url() parts of the static css
  });

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile.replace('.css', '.min.css')), {});
  await writeFile(
    cssFile.replace('.css', '.min.css'),
    `@charset "UTF-8";${cssMin}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
