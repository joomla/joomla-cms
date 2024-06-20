import { chmodSync, existsSync, writeFileSync, mkdirSync, rmSync } from "fs";
import { dirname } from "path";
import { umask } from 'node:process';

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
 * Writes the given content to the file with the given path relative to the CMS root folder.
 *
 * If directory entries from the path do not exist, they are created recursively with the file mask 0o777.
 * If the file already exists, it will be overwritten.
 * Finally, the given file mode or the default 0o444 is set for the given file.
 *
 * @param {string} path The relative file path (e.g. 'images/test-dir/override.jpg')
 * @param {mixed} content The file content
 * @param {object} config The Cypress configuration
 * @param {number} [mode=0o444] The file mode to be used (in octal)
 *
 * @returns null
 */
function writeFile(path, content, config, mode = 0o444) {
  const fullPath = dirname.join(config.env.cmsPath, path);
  // Prologue: Reset process file mode creation mask to ensure the umask value is not subtracted
  const oldmask = umask(0);
  // Create missing parent directories with 'rwxrwxrwx'
  mkdirSync(dirname(fullPath), { recursive: true, mode: 0o777 });
  // Check if the file exists
  if (existsSync(fullPath)) {
    // Set 'rw-rw-rw-' to be able to overwrite the file
    chmodSync(fullPath, 0o666);
  }
  // Write or overwrite the file on relative path with given content
  writeFileSync(fullPath, content);
  // Finally set given file mode or default 'r--r--r--'
  chmodSync(fullPath, mode);
  // Epilogue: Restore process file mode creation mask
  umask(oldmask);

  return null;
}

export { writeFile, deleteFolder };
