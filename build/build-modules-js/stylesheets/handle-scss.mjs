import { writeFile } from 'node:fs/promises';
import { dirname, sep } from 'node:path';

import rtlcss from 'rtlcss';
import { ensureDir } from 'fs-extra';
import { transform as transformCss, Features } from 'lightningcss';
import { compileAsync } from 'sass-embedded';

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

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile), {});
  await writeFile(
    cssFile,
    `@charset "UTF-8";
${contents}`,
    { encoding: 'utf8', mode: 0o644 },
  );

  const { code: cssMin } = transformCss({
    code: Buffer.from(contents),
    minify: true,
    exclude: Features.VendorPrefixes,
  });

  // Ensure the folder exists or create it
  await ensureDir(dirname(cssFile.replace('.css', '.min.css')), {});
  await writeFile(cssFile.replace('.css', '.min.css'), `@charset "UTF-8";${cssMin}`, { encoding: 'utf8', mode: 0o644 });

  // eslint-disable-next-line no-console
  console.log(`✅ SCSS File compiled: ${cssFile}`);
};
