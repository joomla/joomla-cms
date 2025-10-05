import { basename, dirname, sep } from 'node:path';
import { existsSync, mkdirSync } from 'node:fs';
import { cp as copy } from 'node:fs/promises';

import { minifyFile } from './minify.mjs';

export const handleES5File = async (file) => {
  if (file.endsWith('.js')) {
    // ES5 file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    if (!existsSync(dirname(file).replace(`build${sep}media_source`, `media`))) {
      mkdirSync(dirname(file).replace(`build${sep}media_source`, `media`), { recursive: true, mode: 0o755 });
    }
    await copy(
      file,
      file.replace(`build${sep}media_source`, `media`).replace('.es5.js', '.js'),
      { preserveTimestamps: true },
    );
    console.log(`✅ Legacy js file: ${basename(file)}: copied`);

    minifyFile(file.replace(`build${sep}media_source`, `media`).replace('.es5.js', '.js'));
  }
};
