import { persistedStateOptions } from './plugins/persisted-state.es6';

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
const getDrives = (adapterNames, provider) => {
  const drives = [];
  adapterNames.map((name) => drives.push({ root: `${provider}-${name}:/`, displayName: name }));

  return drives;
};

// Load disks from options
const loadedDisks = options.providers.map((disk) => ({
  displayName: disk.displayName,
  drives: getDrives(disk.adapterNames, disk.name),
}));
const defaultDisk = loadedDisks.find((disk) => disk.drives.length > 0
  && disk.drives[0] !== undefined);
if (!defaultDisk) {
  throw new TypeError('No default media drive was found');
}

// Override the storage if we have a path
if (options.currentPath) {
  const storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key));
  if (storedState && storedState.selectedDirectory
    && (storedState.selectedDirectory !== options.currentPath)) {
    storedState.selectedDirectory = options.currentPath;
    persistedStateOptions.storage.setItem(persistedStateOptions.key, JSON.stringify(storedState));
  }
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
  selectedDirectory: options.currentPath || defaultDisk.drives[0].root,
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
};
