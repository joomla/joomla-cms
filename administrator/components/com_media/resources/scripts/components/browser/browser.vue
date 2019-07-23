<template>
    <div>
        <div class="media-browser"
            @dragenter="onDragEnter"
            @drop="onDrop"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            :style="mediaBrowserStyles"
            ref="browserItems">
            <div class="media-dragoutline">
                <span class="fa fa-cloud-upload upload-icon" aria-hidden="true"></span>
                <p>{{ translate('COM_MEDIA_DROP_FILE') }}</p>
            </div>
            <table v-if="listView === 'table'" class="table media-browser-table">
                <caption class="sr-only">{{ sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', currentDirectory) }}</caption>
                <thead class="media-browser-table-head">
                    <tr>
                        <th class="type" scope="col"></th>
                        <th class="name" scope="col">{{ translate('COM_MEDIA_MEDIA_NAME') }}</th>
                        <th class="size" scope="col">{{ translate('COM_MEDIA_MEDIA_SIZE') }}</th>
                        <th class="dimension" scope="col">{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</th>
                        <th class="created" scope="col">{{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}</th>
                        <th class="modified" scope="col">{{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}</th>
                    </tr>
                </thead>
                <media-browser-item-row v-for="item in items" :key="item.path" :item="item"></media-browser-item-row>
            </table>
            <div class="media-browser-grid" v-else-if="listView === 'grid'">
                <div class="media-browser-items" :class="mediaBrowserGridItemsClass">
                    <media-browser-item v-for="item in items" :key="item.path" :item="item"></media-browser-item>
                </div>
            </div>
        </div>
        <media-infobar v-if="!this.isModal" ref="infobar"></media-infobar>
    </div>
</template>

<script>
    import * as types from './../../store/mutation-types';

    export default {
        name: 'media-browser',
        computed: {
            /* Get the contents of the currently selected directory */
            items() {
                const directories = this.$store.getters.getSelectedDirectoryDirectories.sort((a, b) => {
                    // Sort by type and alphabetically
                    return (a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1;
                }).filter( dir => {
                    return dir.name.toLowerCase().includes(this.$store.state.search.toLowerCase())
                });
                const files = this.$store.getters.getSelectedDirectoryFiles.sort((a, b) => {
                    // Sort by type and alphabetically
                    return (a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1;
                }).filter( file => {
                    return file.name.toLowerCase().includes(this.$store.state.search.toLowerCase())
                });

                return [...directories, ...files];
            },
            /* The styles for the media-browser element */
            mediaBrowserStyles() {
                return {
                    width: this.$store.state.showInfoBar ? '75%' : '100%'
                }
            },
            /* The styles for the media-browser element */
            listView() {
                return this.$store.state.listView;
            },
            mediaBrowserGridItemsClass() {
                return {
                    ['media-browser-items-' + this.$store.state.gridSize]: true,
                }
            },
            isModal() {
                return Joomla.getOptions('com_media', {}).isModal;
            },
            currentDirectory() {
                const parts = this.$store.state.selectedDirectory.split('/').filter(crumb => crumb.length !== 0);

                // The first part is the name of the drive, so if we have a folder name display it. Else
				// find the filename
				if (parts.length !== 1) {
					return parts[parts.length - 1];
				}

				let diskName = '';

				this.$store.state.disks.forEach(disk => {
					disk.drives.forEach(drive => {
						if (drive.root === parts[0] + '/') {
							diskName = drive.displayName;
						}
					});
				});

				return diskName;
			}
        },
        methods: {
            /* Unselect all browser items */
            unselectAllBrowserItems(event) {
                const clickedDelete = (event.target.id !== undefined && event.target.id === 'mediaDelete') ? true : false;
                const notClickedBrowserItems = (this.$refs.browserItems && !this.$refs.browserItems.contains(event.target)) || event.target === this.$refs.browserItems;
                const notClickedInfobar = this.$refs.infobar !== undefined && !this.$refs.infobar.$el.contains(event.target);
                const clickedOutside = notClickedBrowserItems && notClickedInfobar && !clickedDelete;
                if (clickedOutside) {
                    this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                }
            },

            // Listeners for drag and drop
            // Fix for Chrome
            onDragEnter(e) {
                e.stopPropagation();
                return false;
            },


            // Notify user when file is over the drop area
            onDragOver(e) {
                e.preventDefault();
                document.querySelector('.media-dragoutline').classList.add('active');
                return false;
            },

            /* Upload files */
            upload(file) {
                // Create a new file reader instance
                let reader = new FileReader();

                // Add the on load callback
                reader.onload = (progressEvent) => {
                    const result = progressEvent.target.result,
                        splitIndex = result.indexOf('base64') + 7,
                        content = result.slice(splitIndex, result.length);

                    // Upload the file
                    this.$store.dispatch('uploadFile', {
                        name: file.name,
                        parent: this.$store.state.selectedDirectory,
                        content: content,
                    });
                };

                reader.readAsDataURL(file);
            },

            // Logic for the dropped file
            onDrop(e) {
                e.preventDefault();

                // Loop through array of files and upload each file
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    for (let i = 0, f; f = e.dataTransfer.files[i]; i++) {
                        document.querySelector('.media-dragoutline').classList.remove('active');
                        this.upload(f);
                    }
                }
                document.querySelector('.media-dragoutline').classList.remove('active');
            },

            // Reset the drop area border
            onDragLeave(e) {
                e.stopPropagation();
                e.preventDefault();
                document.querySelector('.media-dragoutline').classList.remove('active');
                return false;
            },
        },
        created() {
            document.body.addEventListener('click', this.unselectAllBrowserItems, false);
        },
        beforeDestroy() {
            document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
        }
    }
</script>
