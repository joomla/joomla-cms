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
                <p>Drop file(s) to Upload</p>
            </div>
            <div v-if="listView === 'table'" class="media-browser-table">
                <div class="media-browser-table-head">
                    <ul>
                        <li class="type"></li>
                        <li class="name">{{ translate('COM_MEDIA_MEDIA_NAME') }}</li>
                        <li class="size">{{ translate('COM_MEDIA_MEDIA_SIZE') }}</li>
                        <li class="dimension">{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</li>
                        <li class="created">{{ translate('COM_MEDIA_MEDIA_CREATED_AT') }}</li>
                        <li class="modified">{{ translate('COM_MEDIA_MEDIA_MODIFIED_AT') }}</li>
                    </ul>
                </div>
                <media-browser-item v-for="item in items" :key="item.path" :item="item"></media-browser-item>
            </div>
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
            }
        },
        methods: {
            /* Unselect all browser items */
            unselectAllBrowserItems(event) {
                const notClickedBrowserItems = (this.$refs.browserItems && !this.$refs.browserItems.contains(event.target)) || event.target === this.$refs.browserItems;
                const notClickedInfobar = this.$refs.infobar !== undefined && !this.$refs.infobar.$el.contains(event.target);
                const clickedOutside = notClickedBrowserItems && notClickedInfobar;
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
