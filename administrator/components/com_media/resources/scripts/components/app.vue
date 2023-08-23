<template>
  <div
    ref="mmContainer"
    class="media-container"
  >
    <div class="media-sidebar">
      <Disk
        v-for="(disk, index) in disks"
        :key="{index}"
        :uid="{index}"
        :disk="disk"
      />
    </div>
    <div class="media-main">
      <Toolbar />
      <ListView />
    </div>
    <Upload />
    <ModalNewFolder />
    <ModalPreview />
    <ModalRename />
    <ModalShare />
    <ModalDelete />
  </div>
</template>

<script setup>
import {
  computed, onMounted, onUnmounted, ref,
} from 'vue';
import notifications from '../app/Notifications.es6';
import ListView from './browser/browser.vue';
import Disk from './media/disk.vue';
import Toolbar from './toolbar/toolbar.vue';
import Upload from './upload/upload.vue';
import ModalNewFolder from './modals/modalnewfolder.vue';
import ModalPreview from './modals/modalpreview.vue';
import ModalRename from './modals/modalrename.vue';
import ModalShare from './modals/modalshare.vue';
import ModalDelete from './modals/modaldelete.vue';
import { useFileStore } from '../stores/files.es6.js';

const fileStore = useFileStore();
const disks = computed(() => fileStore.disks);
const selectedDirectory = computed(() => fileStore.selectedDirectory);
const selectedItems = computed(() => fileStore.selectedItems);

const mmContainer = ref(null);

// Initial load the data
fileStore.getPathContents(selectedDirectory.value, false, false);

onMounted(() => {
  // Listen to the toolbar events
  // MediaManager.Event.listen('onClickCreateFolder', () => viewSto.showCreateFolderModal());
  MediaManager.Event.listen('onClickDelete', () => {
    if (!selectedItems.value.length) {
      return notifications.error('COM_MEDIA_PLEASE_SELECT_ITEM');
    }

    return showConfirmDeleteModal();
  });
});

onUnmounted(() => {
  // @todo implement the stopListen
  // MediaManager.Event.stopListen('onClickCreateFolder');
  // MediaManager.Event.stopListen('onClickDelete');
});
</script>
