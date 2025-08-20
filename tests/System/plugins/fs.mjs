import {
  chmodSync, existsSync, writeFileSync, mkdirSync, rmSync, copyFileSync,
} from 'node:fs';
import { dirname, join } from 'node:path';
import { umask } from 'node:process';

/**
 * Synchronously deletes a file or folder, relative to cmsPath.
 * If it is a folder and contains content, the content is deleted recursively.
 * It ignores if the path doesn't exist.
 *
 * @param {string} relativePath - File or folder, relative to cmsPath
 * @param {object} config - The Cypress configuration object
 *
 * @returns null
 */
function deleteRelativePath(relativePath, config) {
  const fullPath = join(config.env.cmsPath, relativePath);
  rmSync(fullPath, { recursive: true, force: true });

  return null;
}

/**
 * Writes the given content to the file with the given path relative to the CMS root folder.
 *
 * If directory entries from the path do not exist, they are created recursively with the file mask 0o777.
 * If the file already exists, it will be overwritten.
 * Finally, the given file mode or the default 0o444 is set for the given file.
 *
 * @param {string} relativePath - The relative file path (e.g. 'images/test-dir/override.jpg')
 * @param {mixed} content - The file content
 * @param {object} config - The Cypress configuration object
 * @param {number} [mode=0o444] - The file mode to be used (in octal)
 *
 * @returns {Promise<string>} - A promise that resolves to a success message or rejects with an error
 */
function writeRelativeFile(relativePath, content, config, mode = 0o444) {
  return new Promise((resolve, reject) => {
    try {
      const fullPath = join(config.env.cmsPath, relativePath);
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
      resolve(`File successfully written: ${fullPath}`);
    } catch (error) {
      reject(new Error(`Failed to write file: ${error.message}`));
    }
  });
}

/**
 * Copies a file to a specified path relative to the CMS root folder.
 *
 * If the file already exists, it will be overwritten.
 *
 * @param {string} source - The relative file path of the existing file
 * @param {string} destination - The relative file path of the new file
 * @param {object} config - The Cypress configuration object
 *
 * @returns null
 */
function copyRelativeFile(source, destination, config) {
  const fullSource = join(config.env.cmsPath, source);
  const fullDestination = join(config.env.cmsPath, destination);

  copyFileSync(fullSource, fullDestination);

  return null;
}

export { writeRelativeFile, deleteRelativePath, copyRelativeFile };
