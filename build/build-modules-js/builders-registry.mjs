/**
 * Builders registry for Media resources
 */
import { readdirSync, existsSync } from 'node:fs';
import { readdir } from 'node:fs/promises';
import path from 'node:path';

// List of media resources. A folder (extension) name under media_source/
// By default, is used DefaultModuleBuilder class.
// However, each resource may have own builder.mjs script, for this it should be placed in root of its folder.
export const builders = [
  // External libraries, many extensions depending on it, should be run first
  'vendor',
  // System scripts, should be run before any other extension
  'system',
  // Customised bootstrap
  'vendor/bootstrap',
  // jQuery extras
  'vendor/jquery',
  // Customised short-and-sweet
  'vendor/short-and-sweet',
  // Layout scripts
  'layouts',
  // Legacy scripts
  'legacy',
  // Mailto scripts
  'mailto',
  // Cache scripts
  'cache',
];

// Resolve the component builders
const entriesComponentsAdmin = await readdir(path.join(process.cwd(), 'administrator', 'components'), { withFileTypes: true });
const entriesComponentsSite = await readdir(path.join(process.cwd(), 'components'), { withFileTypes: true });

for (const entry of [
  ...entriesComponentsAdmin.filter(entry => entry.isDirectory() && !entry.name.startsWith('.')),
   ...entriesComponentsSite.filter(entry => entry.isDirectory() && !entry.name.startsWith('.'))
  ]) {
  const componentName = entry.name;
  const componentFolder = `${componentName}`;
  if (!builders.includes(componentFolder) && existsSync(path.join(process.cwd(), 'media_source', componentFolder))) {
    builders.push(componentFolder);
  }
}

// Resolve the module builders
const entriesModulesAdmin = await readdir(path.join(process.cwd(), 'administrator', 'modules'), { withFileTypes: true });
const entriesModulesSite = await readdir(path.join(process.cwd(), 'modules'), { withFileTypes: true });

for (const entry of [
  ...entriesModulesAdmin.filter(entry => entry.isDirectory() && !entry.name.startsWith('.')),
  ...entriesModulesSite.filter(entry => entry.isDirectory() && !entry.name.startsWith('.'))
  ]) {
  const moduleName = entry.name;
  const moduleFolder = `${moduleName}`;
  if (!builders.includes(moduleFolder) && existsSync(path.join(process.cwd(), 'media_source', moduleFolder))) {
    builders.push(moduleFolder);
  }
}

// Resolve the plugin builders
const entriesPlugins = await readdir(path.join(process.cwd(), 'plugins'), { withFileTypes: true });

const directoriesPlugins = entriesPlugins
    .filter(entry => entry.isDirectory() && !entry.name.startsWith('.'));

for (const entry of directoriesPlugins) {
  if (!entry.isDirectory() || entry.name.startsWith('.')) continue;
  const pluginName = entry.name;
  const pluginFolder = `plg_${pluginName}`;

  const _entriesPlugins = readdirSync(path.join(process.cwd(), 'plugins', pluginName), { withFileTypes: true });
  for (const _entry of _entriesPlugins) {
    if (!_entry.isDirectory() || _entry.name.startsWith('.')) continue;
    const _pluginFolder = `${pluginFolder}_${_entry.name}`;
    if (!builders.includes(_pluginFolder) && existsSync(path.join(process.cwd(), 'media_source', _pluginFolder))) {
      builders.push(_pluginFolder);
    }
  }
}

// Resolve the administrator template builders
const entriesTemplatesAdmin = await readdir(path.join(process.cwd(), 'administrator', 'templates'), { withFileTypes: true });
const directoriesTemplatesAdmin = entriesTemplatesAdmin
    .filter(entry => entry.isDirectory() && !entry.name.startsWith('.'));

for (const entry of directoriesTemplatesAdmin) {
  if (!entry.isDirectory() || entry.name.startsWith('.')) continue;
  const templateName = entry.name;
  const templateFolder = `templates/administrator/${templateName}`;
  if (
    !builders.includes(templateFolder)
    && existsSync(path.join(process.cwd(), 'media_source', 'templates', 'administrator', templateName))
  ) {
    builders.push(templateFolder);
  }
}

// Resolve the site template builders
const entriesTemplatesSite = await readdir(path.join(process.cwd(), 'templates'), { withFileTypes: true });
const directoriesTemplatesSite = entriesTemplatesSite
    .filter(entry => entry.isDirectory() && !entry.name.startsWith('.'));

for (const entry of directoriesTemplatesSite) {
  if (!entry.isDirectory() || entry.name.startsWith('.')) continue;
  const templateName = entry.name;
  const templateFolder = `templates/site/${templateName}`;
  if (
    !builders.includes(templateFolder)
    && existsSync(path.join(process.cwd(), 'media_source', 'templates', 'site', templateName))
  ) {
    builders.push(templateFolder);
  }
}

// Additional builders, which are not distributed under media/
builders.push('error-pages');

// Builders which should be completed before any following builder starts.
// Used for mass-execution to prevent collisions.
export const blockingBuilders = [
  // Blocking many extensions depending on it
  'vendor',
  // Blocking because 'error-pages' writes in to the same folder, so 'system' should be completed before that
  'system',
];
