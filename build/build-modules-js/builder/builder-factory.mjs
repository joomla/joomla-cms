/**
 * Builder factory class
 */
import path from 'node:path';
import fs from 'node:fs';
import DefaultModuleBuilder from './default-module-builder.mjs';

export class BuilderFactory{
  constructor(basePath = '', targetPath = '', cmdOptions = {}) {
    this.basePath = basePath;
    this.targetPath = targetPath;
    this.cmdOptions = cmdOptions;
  }

  async createBuilder(name) {
    // Module path
    let modulePath = path.join(this.basePath, name, 'builder.mjs');

    // Check if we have the builder module
    if (!fs.existsSync(modulePath)) {
      // Use default module
      return new DefaultModuleBuilder(name, this.basePath, this.targetPath, this.cmdOptions);
    }

    return import(modulePath).then((module) => {
      return new module.default(name, this.basePath, this.targetPath, this.cmdOptions);
    });
  }
}

/**
 * Create and run the builder
 *
 * @param { Command } program
 * @param { String } name
 * @param { BuilderFactory } factory
 * @param { string[] } tasksToRun
 * @param { boolean } skipUndefinedTask
 * @return { Promise }
 */
export const createAndRunBuilder = async (program, name, factory, tasksToRun = [], skipUndefinedTask = false) => {
  return factory.createBuilder(name)
    .then((builder) => {
      if (!builder.getBuildTasks) {
        program.error(`Builder module for "${name}" should provide "getBuildTasks()" method. This is used to determine which task can be run for the builder.`)
      }
      console.log(`Initialize build [${name}]`);

      // Run tasks for given builder
      const allTasks = builder.getAllTasks ? builder.getAllTasks() : builder.getBuildTasks();
      let lastPromise = Promise.resolve();
      (tasksToRun.length ? tasksToRun : builder.getBuildTasks()).forEach((taskName) => {
        // Check whether the task is allowed for active builder
        if (!allTasks.includes(taskName)) {
          // Show error when the builder and the task was specified, and it is not applicable for active builder.
          if (!skipUndefinedTask) {
            program.error(`Task "${taskName}" is not applicable for "${name}" builder.`);
          }
          return;
        }

        // Execute the task sequentially, this is needed because task may depend on each other
        lastPromise = lastPromise.then(() => {
          console.log(`Start task [${name}::${taskName}]`);

          return builder[taskName]().then(async () => {
            console.log('\x1b[32m%s\x1b[0m', `Completed task [${name}::${taskName}]`);
          }).catch((error) => {
            console.log('\x1b[31m%s\x1b[0m', `Failed Task [${name}::${taskName}]`);
            console.trace(error);
            program.error(error.message);
          });
        });
      });
      return lastPromise;
    }).then(() => {
      console.log('\x1b[32m%s\x1b[0m', `Completed build [${name}]`);
    });
};
