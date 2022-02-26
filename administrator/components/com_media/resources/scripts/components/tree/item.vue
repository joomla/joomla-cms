<template>
  <li
    class="media-tree-item"
    :class="{active: isActive}"
    role="treeitem"
    :aria-level="level"
    :aria-setsize="size"
    :aria-posinset="counter"
    :tabindex="0"
  >
    <a @click.stop.prevent="onItemClick()">
      <span class="item-icon"><span :class="iconClass" /></span>
      <span class="item-name">{{ item.name }}</span>
    </a>
    <transition name="slide-fade">
      <media-tree
        v-if="hasChildren"
        v-show="isOpen"
        :aria-expanded="isOpen ? 'true' : 'false'"
        :root="item.path"
        :level="(level+1)"
      />
    </transition>
  </li>
</template>

<script>
import navigable from '../../mixins/navigable.es6';

export default {
  name: 'MediaTreeItem',
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
    counter: {
      type: Number,
      required: true,
    },
    size: {
      type: Number,
      required: true,
    },
  },
  computed: {
    /* Whether or not the item is active */
    isActive() {
      return (this.item.path === this.$store.state.selectedDirectory);
    },
    /**
             * Whether or not the item is open
             *
             * @return  boolean
             */
    isOpen() {
      return this.$store.state.selectedDirectory.includes(this.item.path);
    },
    /* Whether or not the item has children */
    hasChildren() {
      return this.item.directories.length > 0;
    },
    iconClass() {
      return {
        fas: false,
        'icon-folder': !this.isOpen,
        'icon-folder-open': this.isOpen,
      };
    },
    getTabindex() {
      return this.isActive ? 0 : -1;
    },
  },
  methods: {
    /* Handle the on item click event */
    onItemClick() {
      this.navigateTo(this.item.path);
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
  },
};
</script>
