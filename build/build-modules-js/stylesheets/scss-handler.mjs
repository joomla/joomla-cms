/**
 * SCSS handler
 */
import rtlcss from "rtlcss";
import { compileAsync } from 'sass-embedded';
import { fsp } from "node:fs/promises";
import { composeVisitors, Features, transform as transformCss } from "lightningcss";
import { urlVersioning } from "./css-versioning.mjs";

export const handleScss = async (srcPath, targetPath) => {
  return compileAsync(srcPath).then(async ({ css: content }) => {

    if (targetPath.endsWith('-rtl.css')) {
      content = rtlcss.process(content);
    }

    // Remove @charset "UTF-8" at beginning to preserve the license
    // Because the license comment needs to start at the beginning of the file to be saved
    content = content.startsWith('@charset "UTF-8";\n') ? content.replace('@charset "UTF-8";\n', '') : content;

    const { code: css } = transformCss({
      code: Buffer.from(content),
      minify: false,
      exclude: Features.VendorPrefixes,
      visitor: composeVisitors([urlVersioning(srcPath)]), // Adds a hash to the url() parts of the static css
    });

    // Run url() versioning for the source
    await fsp.writeFile(
      targetPath,
      contents.startsWith('@charset "UTF-8";') ? css : `@charset "UTF-8";
${css}`,
      { encoding: 'utf8', mode: 0o644 },
    );

    // Minify the file
    const { code: cssMin } = transformCss({
      code: Buffer.from(css),
      minify: true,
      exclude: Features.VendorPrefixes,
    });

    return fsp.writeFile(
      targetPath.replace('.css', '.min.css'),
      `@charset "UTF-8";${cssMin}`,
      { encoding: 'utf8', mode: 0o644 },
    );
  });
};
