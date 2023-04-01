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

let currentPath;
const storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key));

// Gracefully use the given path, the session storage state or fall back to sensible default
if (options.currentPath) {
  let useDrive = false;
  Object.values(loadedDisks).forEach((drive) => drive.drives.forEach((curDrive) => {
    if (options.currentPath.indexOf(curDrive.root) === 0) {
      useDrive = true;
    }
  }));

  if (useDrive) {
    if ((storedState && storedState.selectedDirectory && storedState.selectedDirectory !== options.currentPath)) {
      storedState.selectedDirectory = options.currentPath;
      persistedStateOptions.storage.setItem(persistedStateOptions.key, JSON.stringify(storedState));
      currentPath = options.currentPath;
    } else {
      currentPath = options.currentPath;
    }
  } else {
    currentPath = defaultDisk.drives[0].root;
  }
} else if (storedState && storedState.selectedDirectory) {
  currentPath = storedState.selectedDirectory;
}

if (!currentPath) {
  currentPath = defaultDisk.drives[0].root;
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
  selectedDirectory: currentPath,
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
