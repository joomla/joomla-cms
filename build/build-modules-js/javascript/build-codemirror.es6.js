/**
 * Build codemirror modules
 */
/* eslint-disable import/no-extraneous-dependencies */

const {
  existsSync, readFile, writeFile, readdirSync,
} = require('fs-extra');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { minify } = require('terser');

// Find a list of modules for given provider, eg all sub @codemirror/...
const retrieveListOfChildModules = (provider) => {
  const cmModules = [];

  // Get @codemirror module roots
  const roots = [];
  module.paths.forEach((path) => {
    const fullPath = `${path}/${provider}`;
    if (existsSync(fullPath)) {
      roots.push(fullPath);
    }
  });

  // List of modules
  roots.forEach((rootPath) => {
    readdirSync(rootPath).forEach((subModule) => {
      cmModules.push(`${provider}/${subModule}`);
    });
  });

  return cmModules;
};

// Build the module
const buildModule = async (module, externalModules, destFile) => {
  const build = await rollup.rollup({
    input: module,
    external: externalModules || [],
    plugins: [
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': '"production"',
      }),
    ],
  });

  await build.write({
    format: 'es',
    sourcemap: false,
    file: destFile,
  });
  await build.close();
};


// Minify a js file
const createMinified = (filePath) => {
  const destFile = filePath.replace('.js', '.min.js');
  // Read source
  readFile(filePath, { encoding: 'utf8' }).then((src) => {
    // Minify
    minify(src, { sourceMap: false, format: { comments: false } }).then((result) => {
      // Save result
      writeFile(destFile, result.code, { encoding: 'utf8', mode: 0o644 });
    });
  });
};

module.exports.compileCodemirror = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Codemirror Components...');

  const cmModules = retrieveListOfChildModules('@codemirror');
  const lModules = retrieveListOfChildModules('@lezer');
  const externalModules = [...cmModules, ...lModules];
  const destBasePath = 'media/vendor/codemirror/js';

  // Prepare @codemirror modules
  cmModules.forEach((module) => {
    const destFile = `${module.replace('@codemirror/', 'codemirror-')}.js`;
    const destPath = `${destBasePath}/${destFile}`;

    buildModule(module, externalModules, destPath).then(() => {
      createMinified(destPath);
    });
  });

  // Prepare @lezer modules which @codemirror depends on
  lModules.forEach((module) => {
    const destFile = `${module.replace('@lezer/', 'lezer-')}.js`;
    const destPath = `${destBasePath}/${destFile}`;

    buildModule(module, externalModules, destPath).then(() => {
      createMinified(destPath);
    });
  });

  // console.log('compileCodemirror', cmModules, lModules);
};
