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
        role="treeitem"
        aria-level="1"
        :aria-setsize="counter"
        :aria-posinset="1"
        :tabindex="getTabindex"
      >
        <a>
          <span class="item-name">{{ drive.displayName }}</span>
        </a>
        <media-tree
          :root="drive.root"
          :level="2"
        />
      </li>
    </ul>
  </div>
</template>

<script>
import navigable from '../../mixins/navigable.es6';

export default {
  name: 'MediaDrive',
  mixins: [navigable],
  // eslint-disable-next-line vue/require-prop-types
  props: ['drive', 'total', 'diskId', 'counter'],
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
  },
};
</script>
