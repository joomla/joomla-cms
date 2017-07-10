// Get the disks from joomla option storage
const options = Joomla.getOptions('com_media', {});
if (options.providers === undefined || options.providers.length === 0) {
    throw new TypeError('Media providers are not defined.');
}

// The initial state
export default {
    // Will hold the activated filesystem disks
    disks: options.providers.map((disk) => {
        return Object.assign(disk, {
            root: disk.name + ':/',
        })
    }),
    // The loaded directories
    directories: options.providers.map((disk) => {
        return {path: disk.name + ':/', name: disk.displayName, directories: [], files: [], directory: null}
    }),
    // The loaded files
    files: [],
    // The selected disk. Providers are ordered by plugin ordering, so we set the first provider
    // in the list as the default provider.
    selectedDirectory: options.providers[0].name + ':/',
    // The currently selected items
    selectedItems: [],
    // The state of create folder model
    showCreateFolderModal: false
}
