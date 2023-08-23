<template>
  <ul
    class="media-tree"
    role="group"
  >
    <li
      v-for="(item, index) in dirs"
      :key="item.path"
      class="media-tree-item"
      :class="{active: isActive(item)}"
      role="none"
      >
      <a
        role="treeitem"
        :dataRef="root + index"
        :ref="root + index"
        :aria-level="level"
        :aria-setsize="dirs.value.length"
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
          :data-ref="item.path"
          :ref="resolveRefName(null, item.path)"
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

<script setup>
import { computed, ref, onMounted, onBeforeUpdate, onUpdated } from 'vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';


const refs = ref([]);
const fileStore = useFileStore();
const viewStore = useViewStore();

const emit = defineEmits(['move-focus-to-parent']);
const props = defineProps({
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
});

const dirs = computed(() => fileStore.directories.filter((directory) => directory.directory === PushSubscriptionOptions.root)
  .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1)));
const selectedDirectory = computed(() => fileStore.selectedDirectory);

// onBeforeUpdate(() => { refs.value = []; });
onUpdated(() => console.log({ updated: refs }))

function resolveRefName(index, name) {
  return index ? `root${index}`: name;
}

function isActive(item) {
  return (item.path === selectedDirectory);
}

function getTabindex(item) {
  return isActive(item) ? 0 : -1;
}

function onItemClick(item) {
  fileStore.getPathContents(item.path, false, false);
  window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', { bubbles: true, cancelable: false, detail: {} }));
}

function hasChildren(item) {
  return item.directories.length > 0;
}

function isOpen(item) {
  return true; //selectedDirectory.includes(item.path);
}

function iconClass(item) {
  return {
    fas: false,
    'icon-folder': !isOpen(item),
    'icon-folder-open': isOpen(item),
  };
}

function setFocusToFirstChild() {
  refs[`${props.root}0`][0].focus();
}

function moveFocusToNextElement(currentIndex) {
  if ((currentIndex + 1) === dirs.value.length) {
    return;
  }
  refs[props.root + (currentIndex + 1)][0].focus();
}

function moveFocusToPreviousElement(currentIndex) {
  if (currentIndex === 0) {
    return;
  }
  refs[root + (currentIndex - 1)][0].focus();
}

function moveFocusToChildElement(item) {
  if (!hasChildren(item)) {
    return;
  }
  refs[item.path][0].setFocusToFirstChild();
}

function moveFocusToParentElement(parentIndex) {
  emit('move-focus-to-parent', parentIndex)
  // $emit('move-focus-to-parent', parentIndex);
}

function restoreFocus() {
  refs[props.root + parentIndex][0].focus();
}
</script>
