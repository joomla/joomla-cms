<template>
  <div
    class="media-drive"
    @click.stop.prevent="onDriveClick()"
  >
    <ul
      class="media-tree"
      role="tree"
      :aria-labelledby="diskId"
    >
      <li
        :class="{active: isActive, 'media-tree-item': true, 'media-drive-name': true}"
        role="none"
      >
        <a
          ref="drive-root"
          role="treeitem"
          aria-level="1"
          :aria-setsize="counter"
          :aria-posinset="1"
          :tabindex="getTabindex"
          @keyup.right="moveFocusToChildElement(drive.root)"
          @keyup.enter="onDriveClick"
        >
          <span class="item-name">{{ drive.displayName }}</span>
        </a>
        <MediaTree
          :ref="drive.root"
          :root="drive.root"
          :level="2"
          :parent-index="0"
          @move-focus-to-parent="restoreFocus"
        />
      </li>
    </ul>
  </div>
</template>

<script>
import navigable from '../../mixins/navigable.es6';
import MediaTree from './tree.vue';

export default {
  name: 'MediaDrive',
  components: {
    MediaTree,
  },
  mixins: [navigable],
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
  computed: {
    /* Whether or not the item is active */
    isActive() {
      return (this.$store.state.selectedDirectory === this.drive.root);
    },
    getTabindex() {
      return this.isActive ? 0 : -1;
    },
  },
  methods: {
    /* Handle the on drive click event */
    onDriveClick() {
      this.navigateTo(this.drive.root);
    },
    moveFocusToChildElement(nextRoot) {
      this.$refs[nextRoot].setFocusToFirstChild();
    },
    restoreFocus() {
      this.$refs['drive-root'].focus();
    },
  },
};
</script>
