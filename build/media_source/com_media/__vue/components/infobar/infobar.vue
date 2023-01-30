<template>
  <transition name="infobar">
    <div
      v-if="showInfoBar && item"
      class="media-infobar"
    >
      <span
        class="infobar-close"
        @click="hideInfoBar()"
      >×</span>
      <h2>{{ item.name }}</h2>
      <div
        v-if="item.path === '/'"
        class="text-center"
      >
        <span class="icon-file placeholder-icon" />
        Select file or folder to view its details.
      </div>
      <dl v-else>
        <dt>{{ translate('COM_MEDIA_FOLDER') }}</dt>
        <dd>{{ item.directory }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_TYPE') }}</dt>
        <dd v-if="item.type === 'file'">
          {{ translate('COM_MEDIA_FILE') }}
        </dd>
        <dd v-else-if="item.type === 'dir'">
          {{ translate('COM_MEDIA_FOLDER') }}
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}</dt>
        <dd>{{ item.create_date_formatted }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}</dt>
        <dd>{{ item.modified_date_formatted }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_DIMENSION') }}</dt>
        <dd v-if="item.width || item.height">
          {{ item.width }}px * {{ item.height }}px
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_SIZE') }}</dt>
        <dd v-if="item.size">
          {{ (item.size / 1024).toFixed(2) }} KB
        </dd>
        <dd v-else>
          -
        </dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_MIME_TYPE') }}</dt>
        <dd>{{ item.mime_type }}</dd>

        <dt>{{ translate('COM_MEDIA_MEDIA_EXTENSION') }}</dt>
        <dd>{{ item.extension || '-' }}</dd>
      </dl>
    </div>
  </transition>
</template>
<script>
import * as types from '../../store/mutation-types.es6';

export default {
  name: 'MediaInfobar',
  computed: {
    /* Get the item to show in the infobar */
    item() {
      // Check if there are selected items
      const { selectedItems } = this.$store.state;

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
    },
  },
  methods: {
    hideInfoBar() {
      this.$store.commit(types.HIDE_INFOBAR);
    },
  },
};
</script>
