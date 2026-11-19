<template>
  <li
    class="media-drive media-tree-item media-drive-name"
    :class="{active: isActive}"
    role="treeitem"
    :aria-labelledby="treeLabelId(drive.root)"
    aria-level="1"
    :aria-setsize="total"
    :aria-posinset="counter + 1"
    :aria-expanded="hasChildren ? String(isExpanded) : undefined"
    :aria-selected="isActive ? 'true' : 'false'"
    :tabindex="mediaTree.isTabStop(drive.root) ? 0 : -1"
    @click.stop.prevent="onDriveClick()"
    @focus.self="mediaTree.focusedPath = drive.root"
    @keydown.self="onKeydown"
  >
    <a :id="treeLabelId(drive.root)">
      <span class="item-name">{{ drive.displayName }}</span>
    </a>
    <MediaTree
      v-show="isExpanded"
      :root="drive.root"
      :level="2"
    />
  </li>
</template>

<script>
import navigable from '../../mixins/navigable.es6';
import MediaTree from './tree.vue';
import { onTreeItemKeydown, treeLabelId } from './tree-navigation.es6';

export default {
  name: 'MediaDrive',
  components: {
    MediaTree,
  },
  mixins: [navigable],
  inject: ['mediaTree'],
  props: {
    drive: {
      type: Object,
      default: () => {},
    },
    total: {
      type: Number,
      default: 0,
    },
    diskId: {
      type: String,
      default: '',
    },
    counter: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      // The drive is expanded unless the user collapsed it with the keyboard
      collapsed: false,
    };
  },
  computed: {
    /* Whether or not the item is active */
    isActive() {
      return (this.$store.state.selectedDirectory === this.drive.root);
    },
    hasChildren() {
      return this.$store.state.directories.some((directory) => directory.directory === this.drive.root);
    },
    isExpanded() {
      return this.hasChildren && !this.collapsed;
    },
  },
  watch: {
    '$store.state.selectedDirectory': function () {
      this.collapsed = false;
    },
  },
  methods: {
    treeLabelId,
    /* Handle the on drive click event */
    onDriveClick() {
      this.navigateTo(this.drive.root);

      window.parent.document.dispatchEvent(
        new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {
            type: 'dir',
            path: this.drive.root,
          },
        }),
      );
    },
    onKeydown(event) {
      onTreeItemKeydown(event, {
        hasChildren: this.hasChildren,
        expanded: this.isExpanded,
        expand: () => { this.collapsed = false; },
        collapse: () => { this.collapsed = true; },
        activate: () => this.onDriveClick(),
      });
    },
  },
};
</script>
