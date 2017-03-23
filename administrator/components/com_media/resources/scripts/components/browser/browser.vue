<template>
    <div class="media-browser">
        <div class="media-browser-items col-md-8" ref="browserItems">
            <media-browser-item v-for="item in items" :item="item"></media-browser-item>
        </div>
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
                });
                const files = this.$store.getters.getSelectedDirectoryFiles.sort((a, b) => {
                    // Sort by type and alphabetically
                    return (a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1;
                });

                return [...directories, ...files];
            }
        },
        methods: {
            /* Unselect all browser items */
            unselectAllBrowserItems(event) {
                const eventOutside = !this.$refs.browserItems.contains(event.target) || event.target === this.$refs.browserItems;
                if (eventOutside) {
                    this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
                }
            }
        },
        created() {
            document.body.addEventListener('click', this.unselectAllBrowserItems, false);
        },
        beforeDestroy() {
            document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
        }
    }
</script>