/**
 * Build codemirror modules
 */
/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

const {
  existsSync, readFileSync, writeFile, readdirSync,
} = require('fs-extra');
const cliProgress = require('cli-progress');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { minify } = require('terser');
const { resolvePackageFile } = require('../init/common/resolve-package-file');

// Find a list of modules for given scope, eg all sub @codemirror/...
const retrieveListOfChildModules = (scope) => {
  const cmModules = [];

  // Get @codemirror module roots
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
const createMinified = async (filePath) => {
  const destFile = filePath.replace('.js', '.min.js');
  // Read source
  const src = readFileSync(filePath, { encoding: 'utf8' });
  // Minify
  const min = await minify(src, { sourceMap: false, format: { comments: false } });
  // Save result
  await writeFile(destFile, min.code, { encoding: 'utf8', mode: 0o644 });
};

// Update joomla.asset.json for codemirror
const updateAssetRegistry = async (modules, externalModules) => {
  const srcPath = 'build/media_source/plg_editors_codemirror/joomla.asset.json';
  const destPath = 'media/plg_editors_codemirror/joomla.asset.json';

  // Get base JSON and update
  const registry = JSON.parse(readFileSync(srcPath, { encoding: 'utf8' }));

  // Create asset for each module
  modules.forEach((module) => {
    const packageName = module.package;
    const modulePathJson = resolvePackageFile(`${packageName}/package.json`);
    const moduleOptions = require(modulePathJson);
    const asset = {
      type: 'script',
      name: module.package,
      uri: module.uri.replace('.js', '.min.js'),
      importmap: true,
      package: module.package,
      version: moduleOptions.version,
      dependencies: [],
    };

    // Check for known modules to be used as dependency
    if (moduleOptions.dependencies) {
      Object.entries(moduleOptions.dependencies).forEach(([key]) => {
        if (externalModules.includes(key)) {
          asset.dependencies.push(key);
        }
      });
    }

    registry.assets.push(asset);
  });

  // Write assets registry
  await writeFile(
    destPath,
    JSON.stringify(registry, null, 2),
    { encoding: 'utf8', mode: 0o644 },
  );
};

module.exports.compileCodemirror = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Codemirror Components...');

  const cmModules = retrieveListOfChildModules('@codemirror');
  const lModules = retrieveListOfChildModules('@lezer');
  const externalModules = [...cmModules, ...lModules];
  const destBasePath = 'media/vendor/codemirror/js';
  const assets = [];
  const tasks = [];

  const progressBar = new cliProgress.SingleBar({
    stopOnComplete: true,
    format: '{bar} {percentage}% | {value}/{total} files done',
  }, cliProgress.Presets.shades_classic);
  const totalSteps = (cmModules.length + lModules.length) * 2;
  progressBar.start(totalSteps, 0);

  // Prepare @codemirror modules
  cmModules.forEach((module) => {
    const destFile = `${module.replace('@codemirror/', 'codemirror-')}.js`;
    const destPath = `${destBasePath}/${destFile}`;
    assets.push({ package: module, uri: destPath });

    const task = buildModule(module, externalModules, destPath).then(() => {
      progressBar.increment();
      return createMinified(destPath).then(() => {
        progressBar.increment();
      });
    });
    tasks.push(task);
  });

  // Prepare @lezer modules which @codemirror depends on
  lModules.forEach((module) => {
    const destFile = `${module.replace('@lezer/', 'lezer-')}.js`;
    const destPath = `${destBasePath}/${destFile}`;
    assets.push({ package: module, uri: destPath });

    const task2 = buildModule(module, externalModules, destPath).then(() => {
      progressBar.increment();
      return createMinified(destPath).then(() => {
        progressBar.increment();
      });
    });
    tasks.push(task2);
  });
  // console.log('compileCodemirror', cmModules, lModules, tasks);

  return Promise.all(tasks).then(() => {
    progressBar.stop();
    return updateAssetRegistry(assets, externalModules);
  });
};
