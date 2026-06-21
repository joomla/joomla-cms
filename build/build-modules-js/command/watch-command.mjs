/**
 * Watch command module
 */
import path from 'node:path';
import { BuilderFactory } from '../builder/builder-factory.mjs';

const isWatchNotImplementedError = (error) => error?.message?.startsWith('Watch is not implemented for [');

export default async function watchCommand(program, cmdOptions = {}, builders = []) {
  let buildersToWatch = [];
  const unsupportedBuilders = [];
  const getUnsupportedWatchSummary = (names) => `Skipped builders (watch not supported): ${names.join(', ')}`;
  const addUnsupportedBuilder = (name) => {
    if (!unsupportedBuilders.includes(name)) {
      unsupportedBuilders.push(name);
    }
  };
  const handleUnsupportedBuilder = (name) => {
    addUnsupportedBuilder(name);

    if (!cmdOptions.all) {
      program.error(`Builder "${name}" does not support watch mode.`);
    }
  };
  const logUnsupportedBuilders = () => {
    if (!unsupportedBuilders.length) {
      return;
    }

    console.log(
      '\x1b[33m%s\x1b[0m',
      getUnsupportedWatchSummary(unsupportedBuilders),
    );
  };

  if (cmdOptions.all) {
    buildersToWatch = builders;
  } else if (cmdOptions.name) {
    cmdOptions.name.split(',').map((name) => name.trim()).filter(Boolean).forEach((name) => {

      // Check if builder exists
      if (!builders.includes(name)) {
        program.error(`Builder "${name}" does not exists.`);
      }

      buildersToWatch.push(name);
    });
  }

  if (!buildersToWatch.length) {
    logUnsupportedBuilders();
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
      .then(async (builder) => {
        if (!builder.watch || typeof builder.watch !== 'function') {
          handleUnsupportedBuilder(name);
          return;
        }

        console.log(`Initialize watch [${name}]`);

        return builder.watch();
      })
      .catch((error) => {
        if (isWatchNotImplementedError(error)) {
          handleUnsupportedBuilder(name);
          return;
        }

        console.log('\x1b[31m%s\x1b[0m', `Failed Watch [${name}]`);
        console.trace(error);
        program.error(error.message);
      });
    watchers.push(watcher);
  });

  return Promise.all(watchers).then((result) => {
    logUnsupportedBuilders();

    return result;
  });
};
