import { dirname, sep } from 'node:path';

import pkg from 'fs-extra';
import { transform as transformCss, composeVisitors } from 'lightningcss';
import { urlVersioning } from './css-versioning.mjs';

const {
  readFile, writeFile, ensureDir,
} = pkg;

export const handleCssFile = async (file) => {
  const outputFile = file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`);
  try {
    // CSS file, we will process the file and then minify it in place
    // Ensure that the directories exist or create them
    await ensureDir(dirname(outputFile), { recursive: true, mode: 0o755 });

    let content = await readFile(file, { encoding: 'utf8' });

    // To preserve the licence the comment needs to start at the beginning of the file
    content = content.startsWith('@charset "UTF-8";\n') ? content.replace('@charset "UTF-8";\n', '') : content;

    if (file !== outputFile) {
      const { code: css } = transformCss({
        code: Buffer.from(content),
        minify: false,
        visitor: composeVisitors([urlVersioning(file)]), // Adds a hash to the url() parts of the static css
      });

      // Save optimized css file
      await writeFile(
        outputFile,
        content.startsWith('@charset "UTF-8";')
          ? css
          : `@charset "UTF-8";
${css}`,
        { encoding: 'utf8', mode: 0o644 },
      );
    }

    // Process the file and minify it in place
    const { code: cssMin } = transformCss({
      code: Buffer.from(content),
      minify: true,
      visitor: composeVisitors([urlVersioning(outputFile)]), // Adds a hash to the url() parts of the static css
    });

    // Save minified css file
    await writeFile(outputFile.replace('.css', '.min.css'), `@charset "UTF-8";${cssMin}`, { encoding: 'utf8', mode: 0o644 });

    console.log(`✅ CSS file copied/minified: ${file}`);
  } catch (err) {
    console.log(err);
  }
};
