/**
 * Resolve Package Helper
 */
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
export const getPackagesUnderScope = (scope) => {
  const cmModules = new Set();

  // Get list of node.js include paths
  const paths = createRequire(import.meta.url).resolve.paths('node');

  // Get the scope roots
  const roots = [];
  paths.forEach((path) => {
    const fullPath = `${path}/${scope}`;
    if (existsSync(fullPath)) {
      roots.push(fullPath);
    }
  });

  // List of modules
  roots.forEach((rootPath) => {
    readdirSync(rootPath).forEach((subModule) => {
      cmModules.add(`${scope}/${subModule}`);
    });
  });

  return [...cmModules];
};
