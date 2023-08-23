<template>
  <input
    ref="fileInput"
    type="file"
    class="hidden"
    :name="name"
    :multiple="multiple"
    :accept="accept"
    @change="upload"
  >
</template>

<script setup>
import { defineProps, ref } from 'vue';
import { useFileStore } from '../../stores/files.es6.js';

const filesStore = useFileStore();
const fileInput = ref(null);

// Listen to the toolbar upload click event
MediaManager.Event.listen('onClickUpload', () => fileInput.value.click());

const props = defineProps({
  accept: String,
  extensions: Function,
  name: String,
  multiple: Boolean,
});

// Upload files
function upload(e) {
  e.preventDefault();
  const { files } = e.target;

  // Loop through array of files and upload each file
  [...files].forEach((file) => {
    // Create a new file reader instance
    const reader = new FileReader();

    // Add the on load callback
    reader.onload = (progressEvent) => {
      const { result } = progressEvent.target;
      const splitIndex = result.indexOf('base64') + 7;
      const content = result.slice(splitIndex, result.length);

      // Upload the file
      filesStore.uploadFile({
        name: file.name,
        parent: filesStore.selectedDirectory,
        content,
      });
    };

    reader.readAsDataURL(file);
  });
}
</script>
