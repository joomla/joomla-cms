/**
 * CSS handler
 */

import fsp from 'node:fs/promises';
import { composeVisitors, transform as transformCss } from 'lightningcss';
import { urlVersioning } from './css-versioning.mjs';

/**
 * Preprocess Css content
 *
 * @param { String } content
 * @returns { Promise<string> }
 */
export const preprocessCss = async (content = '') => {
  // Remove @charset "UTF-8" at beginning to preserve the license
  // Because the license comment needs to start at the beginning of the file to be saved
  content = content.startsWith('@charset "UTF-8";\n') ? content.replace('@charset "UTF-8";\n', '') : content;

  // Run url() versioning for the source
  const { code: css } = transformCss({
    code: Buffer.from(content),
    minify: false,
    visitor: composeVisitors([urlVersioning(srcPath)]), // Adds a hash to the url() parts of the static css
  });

  return css.toString();
};

/**
 * Minify Css content
 * @param { String } content
 * @returns { Promise<string> }
 */
export const cssMinify = async (content = '') => {
  // Minify the file
  const { code: cssMin } = transformCss({
    code: Buffer.from(css),
    minify: true,
  });

  return cssMin.toString();
};

export const handleCSS = async (srcPath, targetPath) => {
  return fsp.readFile(srcPath, { encoding: 'utf8' }).then(async (content) => {
    const css = await preprocessCss(content);
    const cssMin = await preprocessCss(content);

    const save = fsp.writeFile(
      targetPath,
      content.startsWith('@charset "UTF-8";') ? css : `@charset "UTF-8";
${css}`,
      { encoding: 'utf8', mode: 0o644 },
    );

    // Save minified css file
    const saveMin = fsp.writeFile(
      targetPath.replace('.css', '.min.css'),
      `@charset "UTF-8";${cssMin}`,
      { encoding: 'utf8', mode: 0o644 }
    );

    return Promise.all(save, saveMin);
  });
};


