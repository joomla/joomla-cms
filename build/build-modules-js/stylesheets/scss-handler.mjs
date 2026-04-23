/**
 * SCSS handler
 */
import { compileAsync } from 'sass-embedded';
import { handleAndStoreCSSContent } from './css-handler.mjs';

/**
 * Read source SCSS, compile it to CSS and store it at the destination.
 *
 * @param { String } srcPath
 * @param { String } targetPath
 * @param { boolean } silent
 * @returns { Promise }
 */
export const handleSCSSFile = async (srcPath, targetPath, silent = false) => {
  return compileAsync(srcPath, {
    quietDeps: true,
    silenceDeprecations: silent ? ['if-function', 'import', 'global-builtin', 'color-functions'] : []
  })
    .then(({ css: content }) => {
      return handleAndStoreCSSContent(targetPath, content);
    }).catch((error) => {
      throw new Error(`Processing failed for "${srcPath}".`, { cause: error });
    });
};
