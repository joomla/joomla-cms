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
 * @returns { Promise }
 */
export const handleSCSSFile = async (srcPath, targetPath) => {
  return compileAsync(srcPath).then(({ css: content }) => {
    return handleAndStoreCSSContent(targetPath, content);
  });
};
