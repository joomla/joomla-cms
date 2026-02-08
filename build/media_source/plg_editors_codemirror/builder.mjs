/**
 * Assets Builder
 */

import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";
import DefaultModuleBuilder from '../../build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile, getPackagesUnderScope } from '../../build-modules-js/utils/resolve-package.mjs';
import { compileCodemirror } from '../../build-modules-js/javascript/build-codemirror.mjs';


export default class CodemirrorModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  async js() {
    await super.js();
    return compileCodemirror();
  }
};
