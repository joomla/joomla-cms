<template>
  <div
    class="media-toolbar"
    role="toolbar"
    :aria-label="translate('COM_MEDIA_TOOLBAR_LABEL')"
  >
    <div
      v-if="loading"
      class="media-loader"
    />
    <div class="media-view-icons">
      <input
        ref="mediaToolbarSelectAll"
        type="checkbox"
        class="media-toolbar-icon media-toolbar-select-all"
        :aria-label="translate('COM_MEDIA_SELECT_ALL')"
        @click.stop="toggleSelectAll"
      >
    </div>
    <Breadcrumb />
    <div
      class="media-view-search-input"
      role="search"
    >
      <label
        for="media_search"
        class="visually-hidden"
      >{{ translate('COM_MEDIA_SEARCH') }}</label>
      <input
        id="media_search"
        class="form-control"
        type="text"
        :placeholder="translate('COM_MEDIA_SEARCH')"
        :value="search"
        @input="changeSearch"
      >
    </div>
    <div class="media-view-icons">
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon"
        :class="{ active: sortingOptions }"
        :aria-label="translate('COM_MEDIA_CHANGE_ORDERING')"
        @click="viewStore.toggleSortingOptions"
      >
        <span
          class="fas fa-sort-amount-down-alt"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-decrease-grid-size"
        :class="{disabled: isGridSize('sm')}"
        :aria-label="translate('COM_MEDIA_DECREASE_GRID')"
        @click.stop.prevent="localDecreaseGridSize"
      >
        <span
          class="icon-search-minus"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-increase-grid-size"
        :class="{disabled: isGridSize('xl')}"
        :aria-label="translate('COM_MEDIA_INCREASE_GRID')"
        @click.stop.prevent="localIncreaseGridSize"
      >
        <span
          class="icon-search-plus"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="media-toolbar-icon media-toolbar-list-view"
        :aria-label="translate('COM_MEDIA_TOGGLE_LIST_VIEW')"
        @click.stop.prevent="toggleListView"
      >
        <span
          :class="toggleListViewBtnIcon"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        class="media-toolbar-icon media-toolbar-info"
        :aria-label="translate('COM_MEDIA_TOGGLE_INFO')"
        @click.stop.prevent="viewStore.toggleInfoBar"
      >
        <span
          class="icon-info"
          aria-hidden="true"
        />
      </button>
    </div>
  </div>
  <div
    v-if="isGridView && sortingOptions"
    class="row g-3 pt-2 pb-2 pe-3 justify-content-end"
    style="border-inline-start: 1px solid var(--template-bg-dark-7); margin-left: 0;"
  >
    <div class="col-3">
      <select
        class="form-select"
        :aria-label="translate('COM_MEDIA_ORDER_BY')"
        :value="sortBy"
        @change="changeOrderBy"
      >
        <option value="name">
          {{ translate('COM_MEDIA_MEDIA_NAME') }}
        </option>
        <option value="size">
          {{ translate('COM_MEDIA_MEDIA_SIZE') }}
        </option>
        <option value="dimension">
          {{ translate('COM_MEDIA_MEDIA_DIMENSION') }}
        </option>
        <option value="date_created">
          {{ translate('COM_MEDIA_MEDIA_DATE_CREATED') }}
        </option>
        <option value="date_modified">
          {{ translate('COM_MEDIA_MEDIA_DATE_MODIFIED') }}
        </option>
      </select>
    </div>
    <div class="col-3">
      <select
        class="form-select"
        :aria-label="translate('COM_MEDIA_ORDER_DIRECTION')"
        :value="sortDirection"
        @change="changeOrderDirection"
      >
        <option value="asc">
          {{ translate('COM_MEDIA_ORDER_ASC') }}
        </option>
        <option value="desc">
          {{ translate('COM_MEDIA_ORDER_DESC') }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Breadcrumb from '../breadcrumb/breadcrumb.vue';
import { useFileStore } from '../../stores/files.es6.js';
import { useViewStore } from '../../stores/listview.es6.js';

const mediaToolbarSelectAll = ref(null);
const fileStore = useFileStore();
const viewStore = useViewStore();
const { selectedItems } = storeToRefs(fileStore);
const search = computed(() => fileStore.search);
const getSelectedDirectoryContents = computed(() => fileStore.getSelectedDirectoryContents);
const loading = computed(() => viewStore.loading);
const listView = computed(() => viewStore.listView);
const gridSize = computed(() => viewStore.gridSize);
const sortingOptions = computed(() => viewStore.sortingOptions);
const sortBy = computed(() => viewStore.sortBy);
const sortDirection = computed(() => viewStore.sortDirection);
const isGridView = computed(() => listView.value === 'grid');
const toggleListViewBtnIcon = computed(() => ((isGridView.value) ? 'icon-list' : 'icon-th'));

function toggleListView() {
  viewStore.toggleListView();
}

function localDecreaseGridSize() {
  if (!isGridSize('sm')) {
    viewStore.decreaseGridSize();
  }
}

function localIncreaseGridSize() {
  if (!isGridSize('xl')) {
    viewStore.increaseGridSize();
  }
}

function toggleSelectAll() {
  if (selectedItems.value.length === getSelectedDirectoryContents.value.length) {
    fileStore.resetSelectedItems();
  } else {
    fileStore.addItemsToSelectedItems([...fileStore.getSelectedDirectoryContents]);
  }
  window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', { bubbles: true, cancelable: false, detail: {} }));
}

function isGridSize(size) {
  return gridSize.value === size;
}

function changeSearch(event) {
  fileStore.setSearchQuery(event.target.value);
}

function changeOrderDirection(event) {
  viewStore.updateSortDirection(event.target.value);
}

function changeOrderBy(event) {
  viewStore.updateSortBy(event.target.value);
}

watch(selectedItems, (ev) => {
  if (!([...ev].length === getSelectedDirectoryContents.value.length)) {
    mediaToolbarSelectAll.value.checked = false;
  }
});
</script>
