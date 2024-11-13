const { existsSync, readdirSync } = require('node:fs');

/**
 * Find full path for package file.
 * Replacement for require.resolve(), as it is broken for packages with "exports" property.
 *
 * @param {string} relativePath Relative path to the file to resolve, in format packageName/file-name.js
 * @returns {string|boolean}
 */
module.exports.resolvePackageFile = (relativePath) => {
  for (let i = 0, l = module.paths.length; i < l; i += 1) {
    const path = module.paths[i];
    const fullPath = `${path}/${relativePath}`;
    if (existsSync(fullPath)) {
      return fullPath;
    }
  }

  return false;
};

/**
 * Find a list of modules under given scope,
 * eg: @foobar will look for all submodules @foobar/foo, @foobar/bar
 *
 * @param scope
 * @returns {[]}
 */
module.exports.getPackagesUnderScope = (scope) => {
  const cmModules = [];

  // Get the scope roots
  const roots = [];
  module.paths.forEach((path) => {
    const fullPath = `${path}/${scope}`;
    if (existsSync(fullPath)) {
      roots.push(fullPath);
    }
  });

  // List of modules
  roots.forEach((rootPath) => {
    readdirSync(rootPath).forEach((subModule) => {
      cmModules.push(`${scope}/${subModule}`);
    });
  });

  return cmModules;
};
