import { chmod, writeFileSync, mkdirSync, rmSync } from "fs";
import { dirname } from "path";

/**
 * Deletes a folder with the given path recursive.
 *
 * @param {string} path The path
 * @param {object} config The config
 *
 * @returns null
 */
function deleteFolder(path, config) {
  rmSync(`${config.env.cmsPath}/${path}`, { recursive: true, force: true });

  return null;
}

/**
 * Writes the given content to a file for the given path.
 *
 * @param {string} path The path
 * @param {mixed} content The content
 * @param {object} config The config
 *
 * @returns null
 */
function writeFile(path, content, config) {
  mkdirSync(dirname(`${config.env.cmsPath}/${path}`), { recursive: true, mode: 0o777 });
  chmod(dirname(`${config.env.cmsPath}/${path}`), 0o777);
  writeFileSync(`${config.env.cmsPath}/${path}`, content);
  chmod(`${config.env.cmsPath}/${path}`, 0o777);

  return null;
}

export { writeFile, deleteFolder };
