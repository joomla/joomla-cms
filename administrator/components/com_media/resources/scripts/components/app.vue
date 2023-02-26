<template>
  <div class="media-container">
    <div class="media-sidebar">
      <media-disk
        v-for="(disk, index) in disks"
        :key="index"
        :uid="index"
        :disk="disk"
      />
    </div>
    <div class="media-main">
      <media-toolbar />
      <media-browser />
    </div>
    <media-upload />
    <media-create-folder-modal />
    <media-preview-modal />
    <media-rename-modal />
    <media-share-modal />
    <media-confirm-delete-modal />
  </div>
</template>

<script>
import * as types from '../store/mutation-types.es6';
import { notifications } from '../app/Notifications.es6';

export default {
  name: 'MediaApp',
  data() {
    return {
      // The full height of the app in px
      fullHeight: '',
    };
  },
  computed: {
    disks() {
      return this.$store.state.disks;
    },
  },
  created() {
    // Listen to the toolbar events
    MediaManager.Event.listen('onClickCreateFolder', () => this.$store.commit(types.SHOW_CREATE_FOLDER_MODAL));
    MediaManager.Event.listen('onClickDelete', () => {
      if (this.$store.state.selectedItems.length > 0) {
        this.$store.commit(types.SHOW_CONFIRM_DELETE_MODAL);
      } else {
        notifications.error('COM_MEDIA_PLEASE_SELECT_ITEM');
      }
    });
  },
  mounted() {
    // Set the full height and add event listener when dom is updated
    this.$nextTick(() => {
      this.setFullHeight();
      // Add the global resize event listener
      window.addEventListener('resize', this.setFullHeight);
    });

    // Initial load the data
    this.$store.dispatch('getContents', this.$store.state.selectedDirectory);
  },
  beforeUnmount() {
    // Remove the global resize event listener
    window.removeEventListener('resize', this.setFullHeight);
  },
  methods: {
    /* Set the full height on the app container */
    setFullHeight() {
      this.fullHeight = `${window.innerHeight - this.$el.getBoundingClientRect().top}px`;
    },
  },
};
</script>
