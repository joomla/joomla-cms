/**
 * Assets Builder
 */
import fs from "node:fs";
import path from "node:path";
import fsp from "node:fs/promises";

import DefaultModuleBuilder from '../../../build/build-modules-js/builder/default-module-builder.mjs';

export default class VendorJQueryModuleBuilder extends DefaultModuleBuilder
{
  /**
   * Remove files on target location
   * @returns { Promise }
   */
  async clear() {
    // Bypass clear, it is conflicting with Vendor builder
  }
};
