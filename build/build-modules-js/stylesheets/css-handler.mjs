/**
 * CSS handler
 */

import fsp from 'node:fs/promises';
import { composeVisitors, transform as transformCss } from 'lightningcss';
import { urlVersioning } from './css-versioning.mjs';

export const cssMinify = async (srcPath, targetPath) => {
  return fsp.readFile(srcPath, { encoding: 'utf8' }).then(async (content) => {

    // Remove @charset "UTF-8" at beginning to preserve the license
    // Because the license comment needs to start at the beginning of the file to be saved
    content = content.startsWith('@charset "UTF-8";\n') ? content.replace('@charset "UTF-8";\n', '') : content;

    // Run url() versioning for the source
    const { code: css } = transformCss({
      code: Buffer.from(content),
      minify: false,
      visitor: composeVisitors([urlVersioning(srcPath)]), // Adds a hash to the url() parts of the static css
    });

    await fsp.writeFile(
      targetPath,
      content.startsWith('@charset "UTF-8";') ? css : `@charset "UTF-8";
${css}`,
      { encoding: 'utf8', mode: 0o644 },
    );

    // Minify the file
    const { code: cssMin } = transformCss({
      code: Buffer.from(css),
      minify: true,
    });

    // Save minified css file
    return fsp.writeFile(
      targetPath.replace('.css', '.min.css'),
      `@charset "UTF-8";${cssMin}`,
      { encoding: 'utf8', mode: 0o644 }
    );
  });
};
