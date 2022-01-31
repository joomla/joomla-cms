<template>
  <ul
    class="media-tree"
    role="group"
  >
    <media-tree-item
      v-for="(item, index) in directories"
      :key="item.path"
      :counter="index"
      :item="item"
      :size="directories.length"
      :level="level"
    />
  </ul>
</template>

<script>
export default {
  name: 'MediaTree',
  props: {
    root: {
      type: String,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
  },
  computed: {
    /* Get the directories */
    directories() {
      return this.$store.state.directories
        .filter((directory) => (directory.directory === this.root))
        // Sort alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1));
    },
  },
};
</script>
