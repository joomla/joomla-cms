import persistedStateOptions from './plugins/persisted-state.es6';

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

const storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key));

function setSession(path) {
  persistedStateOptions.storage.setItem(
    persistedStateOptions.key,
    JSON.stringify({ ...storedState, ...{ selectedDirectory: path } }),
  );
}

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

  // Session mismatch
  setSession(path);
  return path;
}

// The initial state
export default {
  // The general loading state
  isLoading: false,
  // Will hold the activated filesystem disks
  disks: loadedDisks,
  // The loaded directories
  directories: loadedDisks.map(() => ({
    path: defaultDisk.drives[0].root,
    name: defaultDisk.displayName,
    directories: [],
    files: [],
    directory: null,
  })),
  // The loaded files
  files: [],
  // The selected disk. Providers are ordered by plugin ordering, so we set the first provider
  // in the list as the default provider and load first drive on it as default
  selectedDirectory: getCurrentPath(),
  // The currently selected items
  selectedItems: [],
  // The state of the infobar
  showInfoBar: false,
  // List view
  listView: 'grid',
  // The size of the grid items
  gridSize: 'md',
  // The state of confirm delete model
  showConfirmDeleteModal: false,
  // The state of create folder model
  showCreateFolderModal: false,
  // The state of preview model
  showPreviewModal: false,
  // The state of share model
  showShareModal: false,
  // The state of  model
  showRenameModal: false,
  // The preview item
  previewItem: null,
  // The Search Query
  search: '',
  // The sorting by
  sortBy: storedState && storedState.sortBy ? storedState.sortBy : 'name',
  // The sorting direction
  sortDirection: storedState && storedState.sortDirection ? storedState.sortDirection : 'asc',
  // Active uploads, [{name: fileName, progress: 0}]
  activeUploads: [],
  // Upload progress, from 0 to 100
  uploadProgress: 0,
};
