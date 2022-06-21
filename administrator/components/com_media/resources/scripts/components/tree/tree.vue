<template>
  <ul
    class="media-tree"
    role="group"
  >
    <li
        v-for="(item, index) in directories"
        :key="item.path"
        class="media-tree-item"
        :class="{active: isActive(item)}"
        role="treeitem"
        :aria-level="level"
        :aria-setsize="directories.length"
        :aria-posinset="index"
        :tabindex="getTabindex(item)"
    >
      <a @click.stop.prevent="onItemClick(item)">
        <span class="item-icon"><span :class="iconClass(item)" /></span>
        <span class="item-name">{{ item.name }}</span>
      </a>
      <transition name="slide-fade">
        <media-tree
            v-if="hasChildren(item)"
            v-show="isOpen(item)"
            :aria-expanded="isOpen(item) ? 'true' : 'false'"
            :root="item.path"
            :level="(level+1)"
        />
      </transition>
    </li>
  </ul>
</template>

<script>
import navigable from '../../mixins/navigable.es6';

export default {
  name: 'MediaTree',
  mixins: [navigable],
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
  methods: {
    isActive(item) {
      return (item.path === this.$store.state.selectedDirectory);
    },
    getTabindex(item) {
      return item.isActive ? 0 : -1;
    },
    onItemClick(item) {
      this.navigateTo(item.path);
      window.parent.document.dispatchEvent(
          new CustomEvent(
              'onMediaFileSelected',
              {
                bubbles: true,
                cancelable: false,
                detail: {},
              },
          ),
      );
    },
    hasChildren(item) {
      return item.directories.length > 0;
    },
    isOpen(item) {
      return this.$store.state.selectedDirectory.includes(item.path);
    },
    iconClass(item) {
      return {
        fas: false,
        'icon-folder': !this.isOpen(item),
        'icon-folder-open': this.isOpen(item),
      };
    },
  }
};
</script>
