/**
 * Build command module
 */

import path from 'node:path';
import { BuilderFactory, createAndRunBuilder } from '../builder/builder-factory.mjs';

/**
 * Build command:
 *  build -a
 *  build -n builder1,builder2
 *  build -t css,js
 *  build -n builder1,builder2 -t css,js
 *
 * @param { Command } program        CMD program instance
 * @param { Object } cmdOptions      Command options and arguments
 * @param { Array } builders         List of builder names
 * @param { Array } blockingBuilders List of blocking builder names
 *
 * @return { Promise }
 */
export default async function buildCommand(program, cmdOptions = {}, builders = [], blockingBuilders = []) {
  // Get list of builders to run
  let buildersToRun = [];
  let runAll = false;
  if (cmdOptions.all) {
    runAll = true;
    buildersToRun = builders;
  } else if (cmdOptions.name) {
    cmdOptions.name.split(',').forEach((name) => {
      // Check if builder exists
      if (!builders.includes(name)) {
        program.error(`Builder "${name}" does not exists.`);
      }
      buildersToRun.push(name);
    });
  }

  // Get list of tasks to run
  let tasksToRun = [];
  if (cmdOptions.task) {
    cmdOptions.task.split(',').forEach((name) => {
      tasksToRun.push(name);
    });
  }

  if (!buildersToRun.length) {
    console.log('Nothing to run. Please specify the builder name or use -a to run all builders.');
    return;
  }

  const factory = new BuilderFactory(
    path.resolve('./media_source'),
    path.resolve('./media'),
    cmdOptions,
  );

  // Create builder queue
  const queue = [];
  buildersToRun.forEach((name) => {
    queue.push([
      name,
      blockingBuilders.includes(name),
    ]);
  });

  // Go through queue and execute each builder.
  // The blocking builder will halt Queue lookup.
  const danglingPromises = [];
  const checkQueue = async () => {
    if (!queue.length) return;
    const [ name, isBlocking ] = queue.shift();

    if (isBlocking) {
      // Halt Queue lookup until the builder completes
      return createAndRunBuilder(program, name, factory, tasksToRun, runAll).then(() => checkQueue())
    }

    // Collect dangling promises to be sure all are resolved in the end
    danglingPromises.push(createAndRunBuilder(program, name, factory, tasksToRun, runAll));

    return checkQueue();
  };

  return checkQueue().then(() => {
    return Promise.all(danglingPromises);
  });
};
