/**
 * Build command module
 */

import path from 'node:path';
import { BuilderFactory } from '../builder/builder-factory.mjs';

/**
 * Build command:
 *  build -a
 *  build -n builder1,builder2
 *  build -t css,js
 *  build -n builder1,builder2 -t css,js
 *
 * @param { Command } program       CMD program instance
 * @param { Object } cmdOptions     Command options and arguments
 * @param { Array } builders        List of builder names
 * @param { Object } pkgOptions     Object of package.json
 * @param { Object } buildSettings  Object of settings.json
 */
export default function buildCommand(program, cmdOptions = {}, builders = [], pkgOptions = {}, buildSettings = {}) {
  // Get list of builders to run
  let buildersToRun = [];
  let runAll = false;
  if (cmdOptions.all) {
    runAll = true;
    buildersToRun = builders;
  } else if (cmdOptions.name) {
    cmdOptions.name.split(',').forEach((name) => {
      // Check if builder exists
      if (builders.includes(name)) {
        buildersToRun.push(name);
      }
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
    console.log('Nothing to run. Please specify the builder name or use -a to run all builders');
    return;
  }

  const factory = new BuilderFactory(
    path.resolve('./build/media_source'),
    path.resolve('./media'),
  );


  // const tasksList = new Listr([], { concurrent: true });
  // const tasksCtx = {};
  // buildersToRun.forEach((name) => {
  //   tasksList.add([{
  //     title: `Building "${name}" ...`,
  //     task: async (ctx, task) => {
  //       return new Promise((resolve) => {
  //         task.output = 'I will push an output. [0]';
  //
  //         setTimeout(() => {
  //           task.output = 'I will push an output. [1]';
  //
  //           resolve();
  //         }, 1000);
  //       });
  //     },
  //   }]);
  // });
  // tasksList.run(tasksCtx);

  // Run each builder
  buildersToRun.forEach((name) => {
    factory.createBuilder(name).then((builder) => {
      if (!builder.getTasks) {
        program.error(`Builder module for "${name}" should implement provide "getTasks()" method. Which used to determine which task can be run for the builder.`)
      }
      console.log(`Initialize build [${name}]`);

      // Run tasks for given builder
      const builderTasks = builder.getTasks();
      let lastPromise = Promise.resolve();
      (tasksToRun.length ? tasksToRun : builderTasks).forEach((taskName) => {
        // Check whether the task is allowed for active builder
        if (!builderTasks.includes(taskName)) {
          // Show error when the builder and the task was specified, and it is not applicable for active builder.
          if (!runAll) {
            program.error(`Task "${taskName}" is not applicable for "${name}" builder.`);
          }
          return;
        }

        // Execute the task sequentially, this is needed because task may depend on each other
        lastPromise = lastPromise.then(() => {
          console.log(`Start task [${name}.${taskName}]`);

          return builder[taskName]().then(async () => {
            console.log('\x1b[32m%s\x1b[0m', `Complete task [${name}.${taskName}]`);
          }).catch((error) => {
            console.log('\x1b[31m%s\x1b[0m', `Task error [${name}.${taskName}]`);
            console.trace(error);
            program.error(error.message);
          });
        });
      });
    });
  });
};
