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
  // Nothing stored in the session, use the root of the first drive
  if (!storedState || !storedState.selectedDirectory) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Check that we have a fragment
  if (!options.currentPath) {
    if (!(storedState || storedState.selectedDirectory)) {
      setSession(defaultDisk.drives[0].root);
      return defaultDisk.drives[0].root;
    }
    options.currentPath = '';
  }

  // Get the fragments
  const fragment = options.currentPath.split(':/');

  // Check that we have a fragment
  if (!fragment.length) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  const drivesTmp = Object.values(loadedDisks).map((drive) => drive.drives);
  const useDrive = drivesTmp.flat().find((drive) => drive.root.startsWith(fragment[0]));

  // Drive doesn't exist
  if (!useDrive) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Session match
  if ((storedState && storedState.selectedDirectory && storedState.selectedDirectory.startsWith(useDrive.root))) {
    setSession(storedState.selectedDirectory);
    return storedState.selectedDirectory;
  }

  // Session missmatch
  setSession(options.currentPath);
  return options.currentPath;
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
};
