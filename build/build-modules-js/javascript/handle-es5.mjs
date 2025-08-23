import { basename, dirname, sep } from 'node:path';

import FsExtra from 'fs-extra';
import { minifyFile } from './minify.mjs';

export const handleES5File = async (file) => {
  if (file.endsWith('.js')) {
    // ES5 file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    await FsExtra.ensureDir(dirname(file).replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`));
    await FsExtra.copy(
      file,
      file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.js'),
      { preserveTimestamps: true },
    );
    // eslint-disable-next-line no-console
    console.log(`✅ Legacy js file: ${basename(file)}: copied`);

    minifyFile(file.replace(`${sep}build${sep}media_source${sep}`, `${sep}media${sep}`).replace('.es5.js', '.js'));
  }
};
