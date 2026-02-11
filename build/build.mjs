/**
 * Script used to build Joomla Web Assets content
 */

import { Command } from 'commander';
import semver from 'semver';
import pkgOptions from '../package.json' with { type: 'json' };
import buildSettings from './build-modules-js/settings.json' with { type: 'json' };
import buildCommand from './build-modules-js/command/build-command.mjs';
import { Timer } from './build-modules-js/utils/timer.mjs';
import watchCommand from "./build-modules-js/command/watch-command.mjs";

// Check minimum Node version
if (semver.gte(semver.minVersion(pkgOptions.engines.node), semver.clean(process.version))) {
  throw new Error(`Node version ${semver.clean(process.version)} is not supported, please upgrade to Node version ${semver.clean(pkgOptions.engines.node)}`);
}

// List of builders
// The folder (extension) name under media_source/
// The builder may provide own builder.mjs script. By default, will be used DefaultModuleBuilder class.
const builders = [
  // Libraries
  'vendor', // Many extensions depending on it, should be run first
  'system',
  'vendor/bootstrap', // Customised bootstrap
  'vendor/jquery', // jQuery extras
  'vendor/short-and-sweet', // Customised short-and-sweet
  'layouts',
  'legacy',
  'mailto',

  // Components
  'cache',
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
  'com_media',
  'com_menus',
  'com_modules',
  'com_scheduler',
  'com_tags',
  'com_templates',
  'com_users',
  'com_workflow',
  'com_wrapper',

  // Modules
  'mod_articles',
  'mod_articles_news',
  'mod_languages',
  'mod_login',
  'mod_menu',
  'mod_quickicon',
  'mod_sampledata',

  // Plugins
  'plg_behaviour_compat6',
  'plg_content_vote',
  'plg_editors-xtd_image',
  'plg_editors_codemirror',
  'plg_editors_none',
  'plg_editors_tinymce',
  'plg_installer_folderinstaller',
  'plg_installer_packageinstaller',
  'plg_installer_urlinstaller',
  'plg_installer_webinstaller',
  'plg_media-action_crop',
  'plg_media-action_resize',
  'plg_media-action_rotate',
  'plg_multifactorauth_email',
  'plg_multifactorauth_fixed',
  'plg_multifactorauth_totp',
  'plg_multifactorauth_webauthn',
  'plg_multifactorauth_yubikey',
  'plg_quickicon_autoupdate',
  'plg_quickicon_eos',
  'plg_quickicon_extensionupdate',
  'plg_quickicon_joomlaupdate',
  'plg_quickicon_overridecheck',
  'plg_quickicon_privacycheck',
  'plg_system_debug',
  'plg_system_guidedtours',
  'plg_system_jooa11y',
  'plg_system_schedulerunner',
  'plg_system_shortcut',
  'plg_system_stats',
  'plg_system_webauthn',
  'plg_user_token',

  // Templates
  'templates/administrator/atum',
  'templates/site/cassiopeia',
  'templates/site/cassiopeia_extended',

  // Additional builders, which is not distributed under media/
  'error-pages',
];
// Builders which should be completed before any following builder start.
// Used for mass-execution to prevent collisions.
const blockingBuilders = [
  'vendor', // Blocking many extensions depending on it
  'system', // Blocking because 'error-pages' writes in to same folder, so 'system' should be completed before that
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
    const bench = new Timer('Build command');
    buildCommand(program, options, builders, blockingBuilders)
      .then(() => bench.stop('Build command'));
  });

program
  .command('watch')
  .description('Watch specified asset and rebuild on changes')
  .option('-n,--name <builder_name,builder_name>', 'builder(s) to watch')
  .action((options) => {
    watchCommand(program, options, builders);
  });

program.parse(process.argv)

