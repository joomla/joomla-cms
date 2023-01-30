<template>
  <div class="media-container">
    <div class="media-sidebar">
      <MediaDisk
        v-for="(disk, index) in disks"
        :key="index"
        :uid="index"
        :disk="disk"
      />
    </div>
    <div class="media-main">
      <MediaToolbar />
      <MediaBrowser />
    </div>
    <MediaUpload />
    <MediaCreateFolderModal />
    <MediaPreviewModal />
    <MediaRenameModal />
    <MediaShareModal />
    <MediaConfirmDeleteModal />
  </div>
</template>

<script>
import * as types from '../store/mutation-types.es6';
import notifications from '../app/Notifications.es6';
import MediaBrowser from './browser/browser.vue';
import MediaDisk from './tree/disk.vue';
import MediaToolbar from './toolbar/toolbar.vue';
import MediaUpload from './upload/upload.vue';
import MediaCreateFolderModal from './modals/create-folder-modal.vue';
import MediaPreviewModal from './modals/preview-modal.vue';
import MediaRenameModal from './modals/rename-modal.vue';
import MediaShareModal from './modals/share-modal.vue';
import MediaConfirmDeleteModal from './modals/confirm-delete-modal.vue';

export default {
  name: 'MediaApp',
  components: {
    MediaBrowser,
    MediaDisk,
    MediaToolbar,
    MediaUpload,
    MediaCreateFolderModal,
    MediaPreviewModal,
    MediaRenameModal,
    MediaShareModal,
    MediaConfirmDeleteModal,
  },
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
