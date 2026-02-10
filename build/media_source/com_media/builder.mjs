/**
 * Assets Builder
 */
import DefaultModuleBuilder from '../../build-modules-js/builder/default-module-builder.mjs';
import { mediaManager } from '../../build-modules-js/javascript/build-com_media-js.mjs';


export default class MediaModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    await super.js();

    // @TODO Move media code here and the source files in to src/
    return mediaManager();
  }
};
