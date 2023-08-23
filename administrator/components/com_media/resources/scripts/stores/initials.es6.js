// Get the disks from joomla option storage
const options = Joomla.getOptions('com_media', {});

if (options.providers === undefined || options.providers.length === 0) {
  throw new TypeError('Media providers are not defined.');
}

/**
 * Get the drives
 *
 * @param  {Array}  adapterNames
 * @param  {String} provider
 *
 * @return {Array}
 */
const getDrives = (adapterNames, provider) => adapterNames.map((name) => ({ root: `${provider}-${name}:/`, displayName: name }));

// Load disks from options
const loadedDisks = options.providers.map((disk) => ({
  displayName: disk.displayName,
  drives: getDrives(disk.adapterNames, disk.name),
}));

const defaultDisk = loadedDisks.find((disk) => disk.drives.length > 0 && disk.drives[0] !== undefined);

if (!defaultDisk) {
  throw new TypeError('No default media drive was found');
}

export const storedState = JSON.parse(window.sessionStorage.getItem('joomla.mediamanager'));
export const setSession = (path) => window.sessionStorage.setItem('joomla.mediamanager', JSON.stringify({ ...storedState, ...{ selectedDirectory: path } }));

// Gracefully use the given path, the session storage state or fall back to sensible default
function getCurrentPath() {
  let path = options.currentPath;

  // Set the path from the session when available
  if (!path && storedState && storedState.selectedDirectory) {
    path = storedState.selectedDirectory;
  }

  // No path available, use the root of the first drive
  if (!path) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Get the fragments
  const fragment = path.split(':/');

  // Check that we have a drive
  if (!fragment.length) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  const drivesTmp = Object.values(loadedDisks).map((drive) => drive.drives);

  // Drive doesn't exist
  if (!drivesTmp.flat().find((drive) => drive.root.startsWith(fragment[0]))) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Session missmatch
  setSession(path);
  return path;
}

export {
  loadedDisks,
  defaultDisk,
  getCurrentPath,
};
