/**
 * Script used to build Joomla Web Assets content
 */

import { Command } from 'commander';
import semver from 'semver';
import { Timer } from './build-modules-js/utils/timer.mjs';
import buildCommand from './build-modules-js/command/build-command.mjs';
import watchCommand from './build-modules-js/command/watch-command.mjs';
import pkgOptions from '../package.json' with { type: 'json' };
import { builders, blockingBuilders } from './build-modules-js/builders-registry.mjs';

// Check minimum Node version
if (semver.gte(semver.minVersion(pkgOptions.engines.node), semver.clean(process.version))) {
  throw new Error(`Node version ${semver.clean(process.version)} is not supported, please upgrade to Node version ${semver.clean(pkgOptions.engines.node)}`);
}

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
  .option('-n,--name <resource_name,resource_name>', 'build specific resource(s)')
  .option('-t,--task <builder_task,builder_task>', 'task(s) to run for specified resource(s)')
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

