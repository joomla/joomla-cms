/**
 * Script used to build Joomla Web Assets content
 */

import { Command } from 'commander';
import semver from 'semver';
import pkgOptions from '../package.json' with { type: 'json' };
import buildSettings from './build-modules-js/settings.json' with { type: 'json' };
import buildCommand from './build-modules-js/command/build-command.mjs';

// Check minimum Node version
if (semver.gte(semver.minVersion(pkgOptions.engines.node), semver.clean(process.version))) {
  throw new Error(`Node version ${semver.clean(process.version)} is not supported, please upgrade to Node version ${semver.clean(pkgOptions.engines.node)}`);
}

// List of builders
const builders = [
  // Libraries
  //'vendor',
  //'system',
  // Components
  'com_actionlogs',
  'com_admin',
  'com_associations',
  'com_banners',
  'com_cache',
  'com_categories',
  'com_config',
  'com_contact',
  'com_content',
  'com_contenthistory',
  'com_cpanel',
  'com_fields',
  'com_finder',
  'com_guidedtours',
  'com_installer',
  'com_joomlaupdate',
  'com_languages',
  'com_mails',
  //'com_media',
  'com_menus',
  'com_modules',
  'com_scheduler',
  'com_tags',
  'com_templates',
  'com_users',
  'com_workflow',
  'com_wrapper',
  // Modules
  // @TODO modules
  // Plugins
  // @TODO plugins
];

// The command line, initialize
const program = new Command();

program
  // Show correct command hint in the Help
  .name('node build/build.mjs')
  .version(pkgOptions.version)
  .addHelpText(
    'after',
    `
Version: ${pkgOptions.version}
`,
  );

program
  .command('builders-list')
  .description('Show list of builders')
  .action(() => {
    console.log(builders.join("\n"));
  });

program
  .command('build')
  .description('Build all or only specified asset')
  .option('-a,--all', 'build all assets')
  .option('-n,--name <builder_name,builder_name>', 'run specified builder(s)')
  .option('-t,--task <builder_task,builder_task>', 'task(s) to run for specified asset')
  .option('--sass-silent', 'hide SASS deprecations and warnings')
  .action((options) => {
    buildCommand(program, options, builders, pkgOptions, buildSettings);
  });

program.parse(process.argv)

