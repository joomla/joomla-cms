const { readdir, readFile, writeFile } = require('fs').promises;

const RootPath = process.cwd();
const knownDirs = [
  'templates/site/cassiopeia',
  'templates/administrator/atum',
];

/**
 * Will scan all the installed extensions and rebuild the cleanUpFolders registry.
 */
const updateSettings = async () => {
  let settings;
  const extensionsScanned = await readdir(`${RootPath}/build/media_source`, { withFileTypes: true });

  const extensions = [...extensionsScanned]
    .filter((x) => x.name !== '.DS_Store' && !['templates', 'vendor', 'cache'].includes(x.name) && x.isDirectory())
    .map((x) => x.name);

  const settingsRaw = await readFile(`${RootPath}/build/build-modules-js/settings.json`, { encode: 'utf8' });
  try {
    settings = JSON.parse(settingsRaw);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error(e);
    process.exit(1);
  }

  if (settings) {
    settings.settings.cleanUpFolders = [...extensions, ...knownDirs];
    await writeFile(`${RootPath}/build/build-modules-js/settings.json`, JSON.stringify(settings, '', 2), { encode: 'utf8' });
  }
};

updateSettings();
