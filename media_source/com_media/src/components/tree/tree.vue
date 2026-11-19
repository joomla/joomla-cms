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
      :aria-labelledby="treeLabelId(item.path)"
      :aria-level="level"
      :aria-setsize="directories.length"
      :aria-posinset="index + 1"
      :aria-expanded="hasChildren(item) ? String(isExpanded(item)) : undefined"
      :aria-selected="isActive(item) ? 'true' : 'false'"
      :tabindex="isTabStop(item.path) ? 0 : -1"
      @focus.self="setFocusedPath(item.path)"
      @keydown.self="onKeydown($event, item)"
    >
      <a
        :id="treeLabelId(item.path)"
        @click.stop.prevent="onItemClick(item)"
      >
        <span class="item-icon"><span
          :class="iconClass(item)"
          aria-hidden="true"
        /></span>
        <span class="item-name">{{ item.name }}</span>
      </a>
      <transition name="slide-fade">
        <MediaTree
          v-if="hasChildren(item)"
          v-show="isExpanded(item)"
          :root="item.path"
          :level="(level+1)"
        />
      </transition>
    </li>
  </ul>
</template>

<script>
import navigable from '../../mixins/navigable.es6';
import { onTreeItemKeydown, treeLabelId } from './tree-navigation.es6';

export default {
  name: 'MediaTree',
  mixins: [navigable],
  inject: ['mediaTree'],
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
  data() {
    return {
      // Folders the user expanded or collapsed with the keyboard: { path: true|false }
      expandedOverride: {},
    };
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
  watch: {
    // After navigating, the open folders follow the current folder again
    '$store.state.selectedDirectory': function () {
      this.expandedOverride = {};
    },
  },
  methods: {
    treeLabelId,
    isActive(item) {
      return (item.path === this.$store.state.selectedDirectory);
    },
    isTabStop(path) {
      return this.mediaTree.isTabStop(path);
    },
    setFocusedPath(path) {
      this.mediaTree.focusedPath = path;
    },
    onItemClick(item) {
      this.navigateTo(item.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {
              type: item.type,
              name: item.name,
              path: item.path,
            },
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
    isExpanded(item) {
      if (!this.hasChildren(item)) {
        return false;
      }

      if (typeof this.expandedOverride[item.path] === 'boolean') {
        return this.expandedOverride[item.path];
      }

      return this.isOpen(item);
    },
    iconClass(item) {
      // Folders with sub folders show their expanded state, other folders are open when they are in the current path
      const open = this.hasChildren(item) ? this.isExpanded(item) : this.isOpen(item);

      return {
        fas: false,
        'icon-folder': !open,
        'icon-folder-open': open,
      };
    },
    onKeydown(event, item) {
      onTreeItemKeydown(event, {
        hasChildren: this.hasChildren(item),
        expanded: this.isExpanded(item),
        expand: () => { this.expandedOverride = { ...this.expandedOverride, [item.path]: true }; },
        collapse: () => { this.expandedOverride = { ...this.expandedOverride, [item.path]: false }; },
        activate: () => this.onItemClick(item),
      });
    },
  },
};
</script>
