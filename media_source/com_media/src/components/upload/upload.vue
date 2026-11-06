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

<script>
export default {
  name: 'MediaUpload',
  props: {
    accept: {
      type: String,
      default: '',
    },
    extensions: {
      type: Function,
      default: () => [],
    },
    name: {
      type: String,
      default: 'file',
    },
    multiple: {
      type: Boolean,
      default: true,
    },
  },
  created() {
    // Listen to the toolbar upload click event
    MediaManager.Event.listen('onClickUpload', () => this.chooseFiles());
  },
  methods: {
    /* Open the choose-file dialog */
    chooseFiles() {
      this.$refs.fileInput.click();
    },
    /* Upload files */
    upload(e) {
      e.preventDefault();
      const { files } = e.target;

      // Loop through array of files and upload each file
      Array.from(files).forEach((file) => {
        this.$store.dispatch('uploadFile', {
          name: file.name,
          parent: this.$store.state.selectedDirectory,
          content: file,
        });
      });
    },
  },
};
</script>
