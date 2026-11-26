<template>
  <div class="media-disk">
    <details open>
      <summary>
        <h2
          :id="diskId"
          class="media-disk-name"
        >
          {{ disk.displayName }}
        </h2>
      </summary>
      <ul
        class="media-tree"
        role="tree"
        :aria-labelledby="diskId"
      >
        <MediaDrive
          v-for="(drive, index) in disk.drives"
          :key="index"
          :disk-id="diskId"
          :counter="index"
          :drive="drive"
          :total="disk.drives.length"
        />
      </ul>
    </details>
  </div>
</template>

<script>
import MediaDrive from './drive.vue';

export default {
  name: 'MediaDisk',
  components: {
    MediaDrive,
  },
  provide() {
    return {
      mediaTree: this.treeState,
    };
  },
  props: {
    disk: {
      type: Object,
      default: () => {},
    },
    uid: {
      type: String,
      default: '',
    },
  },
  data() {
    const vm = this;

    return {
      treeState: {
        // The last focused item is the single tab stop of the tree (roving tabindex)
        focusedPath: null,
        isTabStop(path) {
          if (this.focusedPath) {
            return path === this.focusedPath;
          }

          const current = vm.$store.state.selectedDirectory;
          const roots = vm.disk.drives.map((drive) => drive.root);

          // The current folder, or the first drive when the current folder is on another disk
          return roots.some((root) => current.startsWith(root)) ? path === current : path === roots[0];
        },
      },
    };
  },
  computed: {
    diskId() {
      return `disk-${this.uid + 1}`;
    },
  },
  watch: {
    // After navigating, the tab stop is the current folder again
    '$store.state.selectedDirectory': function () {
      this.treeState.focusedPath = null;
    },
  },
};
</script>
