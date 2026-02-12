/**
 * Watch command module
 */
import path from 'node:path';
import { BuilderFactory } from '../builder/builder-factory.mjs';


export default async function watchCommand(program, cmdOptions = {}, builders = []) {
  let buildersToWatch = [];

  if (cmdOptions.name) {
    cmdOptions.name.split(',').forEach((name) => {
      // Check if builder exists
      if (!builders.includes(name)) {
        program.error(`Builder "${name}" does not exists.`);
      }
      buildersToWatch.push(name);
    });
  }

  if (!buildersToWatch.length) {
    console.log('Nothing to watch. Please specify the builder name to watch.');
    return;
  }

  const factory = new BuilderFactory(
    path.resolve('./media_source'),
    path.resolve('./media'),
    cmdOptions,
  );

  const watchers = [];
  buildersToWatch.forEach((name) => {
    const watcher = factory.createBuilder(name)
      .then((builder) => {
        if (!builder.watch) {
          program.error(`Builder module for "${name}" should implement "watch()" method.`)
        }
        console.log(`Initialize watch [${name}]`);

        return builder.watch().catch((error) => {
          console.log('\x1b[31m%s\x1b[0m', `Failed Watch [${name}]`);
          console.trace(error);
          program.error(error.message);
        });
      });
    watchers.push(watcher);
  });

  return Promise.all(watchers);
};
