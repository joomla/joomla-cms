<template>
    <transition name="infobar">
        <div class="media-infobar" v-if="showInfoBar && item">
            <span class="infobar-close" @click="hideInfoBar()">×</span>
            <h2>{{ item.name }}</h2>
            <div v-if="item.path === '/'" class="text-center">
                <span class="fa fa-file placeholder-icon"></span>
                Select file or folder to view its details.
            </div>
            <dl v-else>
                <dt>{{ translate('COM_MEDIA_FOLDER') }}</dt>
                <dd>{{ item.directory }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_TYPE') }}</dt>
                <dd>{{ item.type || '-' }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_CREATED_AT') }}</dt>
                <dd>{{ item.create_date_formatted }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_MODIFIED_AT') }}</dt>
                <dd>{{ item.modified_date_formatted }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</dt>
                <dd v-if="item.width || item.height">{{ item.width }} x {{ item.height}}</dd>
                <dd v-else>-</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_SIZE') }}</dt>
                <dd>{{ item.size || '-' }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_MIME_TYPE') }}</dt>
                <dd>{{ item.mime_type }}</dd>

                <dt>{{ translate('COM_MEDIA_MEDIA_EXTENSION') }}</dt>
                <dd>{{ item.extension || '-' }}</dd>

            </dl>
        </div>
    </transition>
</template>
<script>
    import * as types from "../../store/mutation-types";

    export default {
        name: 'media-infobar',
        computed: {
            /* Get the item to show in the infobar */
            item() {

                // Check if there are selected items
                const selectedItems = this.$store.state.selectedItems;

                // If there is only one selected item, show that one.
                if (selectedItems.length === 1) {
                    return selectedItems[0];
                }

                // If there are more selected items, use the last one
                if (selectedItems.length > 1) {
                    return selectedItems.slice(-1)[0];
                }

                // Use the currently selected directory as a fallback
                return this.$store.getters.getSelectedDirectory;
            },
            /* Show/Hide the InfoBar */
            showInfoBar() {
                return this.$store.state.showInfoBar;
            }
        },
        methods: {
            hideInfoBar() {
                this.$store.commit(types.HIDE_INFOBAR);
            }
        }
    }
</script>