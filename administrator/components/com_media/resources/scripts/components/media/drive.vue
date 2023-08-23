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
        :class="{active: isActive(), 'media-tree-item': true, 'media-drive-name': true}"
        role="none"
      >
        <a
          ref="drive-root"
          role="treeitem"
          aria-level="1"
          :aria-setsize="counter"
          :aria-posinset="1"
          :tabindex="getTabindex()"
          @keyup.right="moveFocusToChildElement()"
          @keyup.enter="onDriveClick()"
        >
          <span class="item-name">{{ drive.displayName }}</span>
        </a>
        <Tree
          :ref="driveRoot"
          :root="drive.root"
          :level="2"
          :parent-index="0"
          @move-focus-to-parent="restoreFocus()"
        />
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import Tree from './tree.vue';
import { useFileStore } from '../../stores/files.es6.js';

const fileStore = useFileStore();
const selectedDirectory = computed(() => fileStore.selectedDirectory);

const props = defineProps({
  drive: Object,
  total: Number,
  diskId: String,
  counter: Number,
});

const driveRoot = ref(null);

/* Whether or not the item is active */
function isActive() {
  return (selectedDirectory.value === props.drive.root);
}

function getTabindex() {
  return isActive ? 0 : -1;
}

/* Handle the on drive click event */
function onDriveClick() {
  fileStore.getPathContents(props.drive.root, false, false);
}

function moveFocusToChildElement() {
  driveRoot.value.setFocusToFirstChild();
}

function restoreFocus() {
  driveRoot.value.focus();
}
</script>
