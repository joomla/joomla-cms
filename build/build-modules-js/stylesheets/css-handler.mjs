/**
 * CSS handler
 */

import rtlcss from 'rtlcss';
import path from 'node:path';
import fsp from 'node:fs/promises';
import fs from "node:fs";

import { composeVisitors, transform as transformCss } from 'lightningcss';
import { urlVersioning2 } from './css-versioning.mjs';
import { createHash } from "node:crypto";

/**
 * Preprocess Css content
 *
 * @param { String } content
 * @returns { Promise<string> }
 */
export const preprocessCSS = async (content = '') => {
  // Remove @charset "UTF-8" at beginning to preserve the license
  // Because the license comment needs to start at the beginning of the file to be saved
  content = content.startsWith('@charset "UTF-8";\n') ? content.replace('@charset "UTF-8";\n', '') : content;

  // Check the header comments and make sure it starts with /*!
  // This need to force lightningcss to keep the license comments
  if (content.substring(0, 50).includes('/**')) {
    content = content.replace('/**', '/*!');
  }

  // Run url() versioning for the source
  const hash = createHash('md5');
  hash.update(content);
  const hashStr = hash.digest('hex').substring(0, 6);

  const { code: css } = transformCss({
    code: Buffer.from(content),
    minify: false,
    visitor: composeVisitors([ urlVersioning2(hashStr) ]), // Adds a hash to the url() parts of the static css
  });

  return css.toString();
};

/**
 * Minify Css content
 * @param { String } content
 * @returns { Promise<string> }
 */
export const minifyCSS = async (content = '') => {

  const { code: cssMin } = transformCss({
    code: Buffer.from(content),
    minify: true,
  });

  return cssMin.toString();
};

/**
 * Handle CSS content and store it at the destination.
 * Store source and minified version.
 *
 * @param { String } targetPath
 * @param { String } content
 * @returns { Promise }
 */
export const handleAndStoreCSSContent = async (targetPath, content = '') => {
  if (targetPath.endsWith('-rtl.css')) {
    content = rtlcss.process(content);
  }

  const css = await preprocessCSS(content);
  const cssMin = await minifyCSS(css);
  const targetFolder = path.dirname(targetPath);

  if (!fs.existsSync(targetFolder)) {
    fs.mkdirSync(targetFolder, { mode: 0o755, recursive: true });
  }

  const save = fsp.writeFile(
    targetPath,
    `@charset "UTF-8";\n${css}`, // Force "UTF-8" for all, also it is removed by preprocessCSS
    { encoding: 'utf8', mode: 0o644 },
  );

  // Save minified css file
  const saveMin = fsp.writeFile(
    targetPath.replace('.css', '.min.css'),
    `@charset "UTF-8";${cssMin}`,
    { encoding: 'utf8', mode: 0o644 }
  );

  return Promise.all([save, saveMin]);
};

/**
 * Read source CSS, handle its content and store it at the destination.
 *
 * @param { String } srcPath
 * @param { String } targetPath
 * @returns { Promise }
 */
export const handleCSSFile = async (srcPath, targetPath) => {
  return fsp.readFile(srcPath, { encoding: 'utf8' }).then((content) => {
    return handleAndStoreCSSContent(targetPath, content);
  }).catch((error) => {
    throw new Error(`Processing failed for "${srcPath}".`, { cause: error });
  });
};


