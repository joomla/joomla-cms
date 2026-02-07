/**
 * Resolve Package Helper
 */
import path from 'node:path';
import { existsSync, readdirSync } from 'node:fs';
import { createRequire } from 'node:module';

/**
 * Find full path for package file.
 * Replacement for require.resolve(), as it is broken for packages with "exports" property.
 *
 * @param {string} relativePath Relative path to the file to resolve, in format packageName/file-name.js
 * @returns {string|boolean}
 */
export const resolvePackageFile = (relativePath) => {
  // Get list of node.js include paths
  const paths = createRequire(import.meta.url).resolve.paths('node');

  for (let i = 0, l = paths.length; i < l; i += 1) {
    const fullPath = path.join(paths[i], relativePath);

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
export const getPackagesUnderScope = (scope) => {
  const cmModules = new Set();

  // Get list of node.js include paths
  const paths = createRequire(import.meta.url).resolve.paths('node');

  // Get the scope roots
  const roots = [];
  paths.forEach((pathBase) => {
    const fullPath = path.join(pathBase, scope);
    if (existsSync(fullPath)) {
      roots.push(fullPath);
    }
  });

  // List of modules
  roots.forEach((rootPath) => {
    readdirSync(rootPath).forEach((subModule) => {
      cmModules.add(path.join(scope, subModule));
    });
  });

  return [...cmModules];
};
