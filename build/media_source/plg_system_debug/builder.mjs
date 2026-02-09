/**
 * Assets Builder
 */
import path from 'node:path';
import fsp from "node:fs/promises";
import fs from "node:fs";

import pkgOptions from '../../../package.json' with { type: 'json' };
import buildSettings from '../../build-modules-js/settings.json' with { type: 'json' };
import DefaultModuleBuilder from '../../build-modules-js/builder/default-module-builder.mjs';
import { resolvePackageFile } from '../../build-modules-js/utils/resolve-package.mjs';
import { handleJSFile } from '../../build-modules-js/javascript/js-handle.mjs';


export default class PlgDebugModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Copy files to target location.
   *
   * @returns { Promise }
   */
  async copy() {
    await super.copy();
  }


  /**
   * Process JavaScript files and Modules
   * @returns { Promise }
   */
  // async js() {}
};
