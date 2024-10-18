/**
 * Build codemirror modules
 */
/* eslint-disable import/no-extraneous-dependencies, global-require, import/no-dynamic-require */

import { readFileSync } from 'node:fs';
import { writeFile } from 'node:fs/promises';
import { createRequire } from 'node:module';

import cliProgress from 'cli-progress';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import replace from '@rollup/plugin-replace';
import { transform } from 'esbuild';

const require = createRequire(import.meta.url);

const {
  resolvePackageFile,
  getPackagesUnderScope,
} = require('../init/common/resolve-package.cjs');

// Build the module
const buildModule = async (module, externalModules, destFile) => {
  const build = await rollup({
    input: module,
    external: externalModules || [],
    plugins: [
      nodeResolve(),
      replace({ preventAssignment: true, 'process.env.NODE_ENV': '"production"' }),
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
  const min = await transform(src, { minify: true });
  // Save result
  await writeFile(destFile, min.code, { encoding: 'utf8', mode: 0o644 });
};

// Update joomla.asset.json for codemirror
const updateAssetRegistry = async (modules, externalModules) => {
  const srcPath = 'build/media_source/plg_editors_codemirror/joomla.asset.json';
  const destPath = 'media/plg_editors_codemirror/joomla.asset.json';

  // Get base JSON and update
  const registry = JSON.parse(readFileSync(srcPath, { encoding: 'utf8' }));

  // Add dependencies to base codemirror asset
  registry.assets.forEach((asset) => {
    if (asset.name === 'codemirror' && asset.type === 'script') {
      asset.dependencies = externalModules;
    }
  });

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
      version: moduleOptions.version,
    };

    registry.assets.push(asset);
  });

  // Write assets registry
  await writeFile(destPath, JSON.stringify(registry, null, 2), { encoding: 'utf8', mode: 0o644 });
};

export const compileCodemirror = async () => {
  // eslint-disable-next-line no-console
  console.log('Building Codemirror Components...');

  const cmModules = getPackagesUnderScope('@codemirror');
  const lModules = getPackagesUnderScope('@lezer');
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

  return Promise.all(tasks).then(() => {
    progressBar.stop();
    return updateAssetRegistry(assets, externalModules);
  });
};
