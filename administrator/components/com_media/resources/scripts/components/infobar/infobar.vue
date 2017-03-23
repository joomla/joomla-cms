<template>
    <div class="media-infobar">
        <h2>{{ item.name }}</h2>
        <div v-if="item.path === '/'" class="text-center">
            <span class="fa fa-info"></span>
            Select file or folder to view its details.
        </div>
        <dl v-else class="row">
            <dt class="col-sm-4">{{ translate('COM_MEDIA_FOLDER') }}</dt>
            <dd class="col-sm-8">{{ item.directory }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_TYPE') }}</dt>
            <dd class="col-sm-8">{{ item.type || '-' }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_CREATED_AT') }}</dt>
            <dd class="col-sm-8">{{ item.create_date_formatted }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_MODIFIED_AT') }}</dt>
            <dd class="col-sm-8">{{ item.modified_date_formatted }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</dt>
            <dd class="col-sm-8" v-if="item.width || item.height">{{ item.width }} x {{ item.height}}</dd>
            <dd class="col-sm-8" v-else>-</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_SIZE') }}</dt>
            <dd class="col-sm-8">{{ item.size || '-' }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_MIME_TYPE') }}</dt>
            <dd class="col-sm-8">{{ item.mime_type }}</dd>

            <dt class="col-sm-4">{{ translate('COM_MEDIA_MEDIA_EXTENSION') }}</dt>
            <dd class="col-sm-8">{{ item.extension || '-' }}</dd>

        </dl>
    </div>
</template>

<script>
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
            }
        }
    }
</script>