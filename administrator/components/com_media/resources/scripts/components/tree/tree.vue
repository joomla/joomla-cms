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
      role="none"
    >
      <a
        :ref="root + index"
        role="treeitem"
        :aria-level="level"
        :aria-setsize="directories.length"
        :aria-posinset="index"
        :tabindex="getTabindex(item)"
        @click.stop.prevent="onItemClick(item)"
        @keyup.up="moveFocusToPreviousElement(index)"
        @keyup.down="moveFocusToNextElement(index)"
        @keyup.enter="onItemClick(item)"
        @keyup.right="moveFocusToChildElement(item)"
        @keyup.left="moveFocusToParentElement()"
      >
        <span class="item-icon"><span :class="iconClass(item)" /></span>
        <span class="item-name">{{ item.name }}</span>
      </a>
      <transition name="slide-fade">
        <MediaTree
          v-if="hasChildren(item)"
          v-show="isOpen(item)"
          :ref="item.path"
          :aria-expanded="isOpen(item) ? 'true' : 'false'"
          :root="item.path"
          :level="(level+1)"
          :parent-index="index"
          @move-focus-to-parent="restoreFocus"
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
    parentIndex: {
      type: Number,
      required: true,
    },
  },
  emits: ['move-focus-to-parent'],
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
      return this.isActive(item) ? 0 : -1;
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
    setFocusToFirstChild() {
      this.$refs[`${this.root}0`][0].focus();
    },
    moveFocusToNextElement(currentIndex) {
      if ((currentIndex + 1) === this.directories.length) {
        return;
      }
      this.$refs[this.root + (currentIndex + 1)][0].focus();
    },
    moveFocusToPreviousElement(currentIndex) {
      if (currentIndex === 0) {
        return;
      }
      this.$refs[this.root + (currentIndex - 1)][0].focus();
    },
    moveFocusToChildElement(item) {
      if (!this.hasChildren(item)) {
        return;
      }
      this.$refs[item.path][0].setFocusToFirstChild();
    },
    moveFocusToParentElement() {
      this.$emit('move-focus-to-parent', this.parentIndex);
    },
    restoreFocus(parentIndex) {
      this.$refs[this.root + parentIndex][0].focus();
    },
  },
};
</script>
