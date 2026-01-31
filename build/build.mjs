/**
 * Script used to build Joomla Web Assets content
 */

import { Command } from 'commander';
import semver from 'semver';
import path from 'node:path';
import pkgOptions from '../package.json' with { type: 'json' };
import { BuilderFactory } from './build-modules-js/builder/builder-factory.mjs';

// Check minimum Node version
if (semver.gte(semver.minVersion(pkgOptions.engines.node), semver.clean(process.version))) {
  throw new Error(`Node version ${semver.clean(process.version)} is not supported, please upgrade to Node version ${semver.clean(pkgOptions.engines.node)}`);
}

// List of builders
const builders = [
  // Libraries
  'vendor',
  'system',
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
  .action((options) => {
    // Get list of builders to run
    let buildersToRun = [];
    let runAll = false;
    if (options.all) {
      runAll = true;
      buildersToRun = builders;
    } else if (options.name) {
      options.name.split(',').forEach((name) => {
        // Check if builder exists
        if (builders.includes(name)) {
          buildersToRun.push(name);
        }
      });
    }

    // Get list of tasks to run
    let tasksToRun = [];
    if (options.task) {
      options.task.split(',').forEach((name) => {
          tasksToRun.push(name);
      });
    }

    if (!buildersToRun.length) {
      console.log('Nothing to run. Please specify the builder name or use -a to run all builders');
      return;
    }

    const factory = new BuilderFactory(
      path.resolve('./build/media_source'),
      path.resolve('./media'),
    );

    // Run each builder
    buildersToRun.forEach((name) => {
      factory.createBuilder(name).then((builder) => {
        if (!builder.getTasks) {
          program.error(`Builder module for "${name}" should implement provide "getTasks()" method. Which used to determine which task can be run for the builder.`)
        }
console.log(builder)
        // Run tasks for given builder
        const builderTasks = builder.getTasks();
        let lastPromise = Promise.resolve();
        (tasksToRun.length ? tasksToRun : builderTasks).forEach((taskName) => {
          // Check whether the task is allowed for active builder
          if (!builderTasks.includes(taskName)) {
            // Show error when the builder and the task was specified, and it is not applicable for active builder.
            if (!runAll) {
              program.error(`Task "${taskName}" is not applicable for "${name}" builder.`)
            }
            return;
          }

          // Execute the task sequentially, this is needed because task may depend on each other
          lastPromise = lastPromise.then(() => builder[taskName]());
        });
      });
    });
  });

program.parse(process.argv)

